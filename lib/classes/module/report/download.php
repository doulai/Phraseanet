<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2014 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Alchemy\Phrasea\Application;

class module_report_download extends module_report
{
    protected $cor_query = [
        'user'      => 'log.user',
        'site'      => 'log.site',
        'societe'   => 'log.societe',
        'pays'      => 'log.pays',
        'activite'  => 'log.activite',
        'fonction'  => 'log.fonction',
        'usrid'     => 'log.usrid',
        'coll_id'   => 'log_colls.coll_id',
        'ddate'     => "log_docs.date",
        'id'        => 'log_docs.id',
        'log_id'    => 'log_docs.log_id',
        'record_id' => 'log_docs.record_id',
        'final'     => 'log_docs.final',
        'comment'   => 'log_docs.comment',
        'size'      => 'subdef.size',
        'mime'      => 'subdef.mime',
        'file'      => 'subdef.file'
    ];

    /**
     * constructor
     *
     * @param Application $app
     * @param string      $arg1    start date of the  report
     * @param string      $arg2    end date of the report
     * @param integer     $sbas_id id of the databox
     * @param string      $collist
     */
    public function __construct(Application $app, $arg1, $arg2, $sbas_id, $collist)
    {
        parent::__construct($app, $arg1, $arg2, $sbas_id, $collist);
        $this->title = $this->app->trans('report:: telechargements');
    }

    /**
     * @desc build the specified requete
     * @param $obj $conn the current connection to databox
     * @return string
     */
    protected function buildReq($groupby = false, $on = false)
    {
        $sql = $this->sqlBuilder('download')
                ->setOn($on)->setGroupBy($groupby)->buildSql();

        $this->req = $sql->getSql();
        $this->params = $sql->getParams();
        $this->total = $sql->getTotalRows();
    }

    public function colFilter($field, $on = false)
    {
        $ret = [];
        $sqlBuilder = $this->sqlBuilder('download');
        $var = $sqlBuilder->sqlDistinctValByField($field);
        $sql = $var['sql'];
        $params = $var['params'];

        $stmt = $sqlBuilder->getConnBas()->prepare($sql);
        $stmt->execute($params);
        $rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        foreach ($rs as $row) {
            $value = $row['val'];
            if ($field == 'coll_id') {
                $caption = phrasea::bas_labels(phrasea::baseFromColl($this->sbas_id, $value, $this->app), $this->app);
            } elseif ($field == 'ddate')
                $caption = $this->app['date-formatter']->getPrettyString(new DateTime($value));
            elseif ($field == 'size')
                $caption = p4string::format_octets($value);
            else
                $caption = $value;
            $ret[] = ['val'   => $caption, 'value' => $value];
        }

        return $ret;
    }

    /**
     * @desc build the result from the specified sql
     * @param array  $champ all the field from the request displayed in a array
     * @param string $sql   the request from buildreq
     */
    protected function buildResult(Application $app, $rs)
    {
        $i = 0;
        $pref = parent::getPreff($app, $this->sbas_id);

        foreach ($rs as $row) {
            if ($this->enable_limit && ($i > $this->nb_record))
                break;

            foreach ($this->champ as $column) {
                $this->formatResult($column, $row[$column], $i);
            }

            if (array_key_exists('record_id', $row)) {
                try {
                    $record = new \record_adapter($app, $this->sbas_id, $row['record_id']);
                } catch (Exception_Record_AdapterNotFound $e) {
                    continue;
                }

                foreach ($pref as $field) {
                    try {
                        $this->result[$i][$field] = $record->get_caption()
                            ->get_field($field)
                            ->get_serialized_values();
                    } catch (\Exception $e) {
                        $this->result[$i][$field] = '';
                    }
                }
            }
            $i ++;
        }
    }

    private function formatResult($column, $value, $i)
    {
        if ($value) {
            if ($column == 'coll_id')
                $this->result[$i][$column] = $this->formatCollId($value);
            elseif ($column == 'ddate')
                $this->result[$i][$column] = $this->formatDateValue($value);
            elseif ($column == 'size')
                $this->result[$i][$column] = p4string::format_octets($value);
            else
                $this->result[$i][$column] = $value;
        } else {
            if ($column == 'comment')
                $this->result[$i][$column] = '';
            else
                $this->result[$i][$column] = $this->formatEmptyValue();
        }
    }

    private function formatEmptyValue()
    {
        return '<i>' . $this->app->trans('report:: non-renseigne') . '</i>';
    }

    private function formatDateValue($value)
    {
        $datetime = new DateTime($value);
        $dateString = $datetime->format(DATE_ATOM);

        return $this->pretty_string ?
            $this->app['date-formatter']->getPrettyString($datetime) : $dateString;
    }

    private function formatCollId($value)
    {
        return phrasea::bas_labels(phrasea::baseFromColl($this->sbas_id, $value, $this->app), $this->app);
    }

    public static function getNbDl(Application $app, $dmin, $dmax, $sbas_id, $list_coll_id)
    {
        $conn = connection::getPDOConnection($app, $sbas_id);

        $params = [':site_id'  => $app['conf']->get(['main', 'key'])];
        $datefilter = module_report_sqlfilter::constructDateFilter($dmin, $dmax);
        $collfilter = module_report_sqlfilter::constructCollectionFilter($app, $list_coll_id);
        $params = array_merge($params, $datefilter['params'], $collfilter['params']);

        $finalfilter = $datefilter['sql'] . ' AND ';
        $finalfilter .= $collfilter['sql'] . ' AND ';
        $finalfilter .= 'log.site = :site_id';

        $sql = '
            SELECT SUM(1) AS nb
            FROM (
                SELECT DISTINCT(log.id)
                FROM log FORCE INDEX (date_site)
                    INNER JOIN log_colls FORCE INDEX (couple) ON (log.id = log_colls.log_id)
                    INNER JOIN log_docs as log_date ON (log.id = log_date.log_id)
                WHERE ' . $finalfilter . '
                AND (
                    log_date.action = \'download\'
                    OR log_date.action = \'mail\'
                )
            ) AS tt
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $row ? $row['nb'] : 0;
    }

    public static function getTopDl(Application $app, $dmin, $dmax, $sbas_id, $list_coll_id)
    {
        $databox = $app['phraseanet.appbox']->get_databox((int) $sbas_id);
        $conn = $databox->get_connection();

        $params = [':site_id'  => $app['conf']->get(['main', 'key'])];
        $datefilter = module_report_sqlfilter::constructDateFilter($dmin, $dmax);
        $collfilter = module_report_sqlfilter::constructCollectionFilter($app, $list_coll_id);
        $params = array_merge($params, $datefilter['params'], $collfilter['params']);

        $finalfilter = "";
        $array = [
            'preview' => [],
            'document' => []
        ];

        $finalfilter .= $datefilter['sql'] . ' AND ';
        $finalfilter .= $collfilter['sql'] . ' AND ';
        $finalfilter .= 'log.site = :site_id';

        $sql = '
            SELECT tt.id, tt.name, SUM(1) AS nb
            FROM (
                SELECT DISTINCT(log.id) AS log_id, log_date.record_id as id, subdef.name
                FROM ( log )
                    INNER JOIN log_colls FORCE INDEX (couple) ON (log.id = log_colls.log_id)
                    INNER JOIN log_docs as log_date  ON (log.id = log_date.log_id)
                    INNER JOIN subdef ON (log_date.record_id = subdef.record_id)
                WHERE (
                        ' . $finalfilter . '
                )
                AND ( log_date.action = \'download\'
                    OR log_date.action = \'mail\'
                )
                AND subdef.name = log_date.final
            ) AS tt
            GROUP BY id, name
        ';

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        foreach ($rs as $row) {
            $record = $databox->get_record($row['id']);

            $k = $row['id'] . '_' . $sbas_id;
            $orig_name = $record->get_original_name();

            if ($row['name'] == 'document') {
                $array[$row['name']][$k]['nb'] = (int) $row['nb'];
                $array[$row['name']][$k]['lib'] = $orig_name;
                $array[$row['name']][$k]['sbasid'] = $sbas_id;
                $array[$row['name']][$k]['id'] = $row['id'];
            } elseif ($row['name'] == "preview") {
                $array[$row['name']][$k]['nb'] = (int) $row['nb'];
                $array[$row['name']][$k]['lib'] = $orig_name;
                $array[$row['name']][$k]['sbasid'] = $sbas_id;
                $array[$row['name']][$k]['id'] = $row['id'];
            }
        }

        return $array;
    }
}
