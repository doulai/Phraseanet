<?php

/*
 * This file is part of Phraseanet
 *
 * (c) 2005-2014 Alchemy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\Phrasea\Core\Provider;

use Alchemy\Phrasea\Form\Constraint\NewLogin;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['registration.fields'] = $app->share(function (Application $app) {
            return $app['conf']->get('registration-fields', []);
        });

        $app['registration.enabled'] = $app->share(function (Application $app) {
            require_once __DIR__ . '/../../../../classes/deprecated/inscript.api.php';

            $bases = giveMeBases($app);

            if ($bases) {
                foreach ($bases as $base) {
                    if ($base['inscript']) {
                        return true;
                    }
                }
            }

            return false;
        });

        $app['registration.optional-fields'] = $app->share(function (Application $app) {
            return [
                'login'=> [
                    'label'       => 'admin::compte-utilisateur identifiant',
                    'type'        => 'text',
                    'constraints' => [
                        new Assert\NotBlank(),
                        new NewLogin($app),
                    ]
                ],
                'gender' => [
                    'label'   => 'admin::compte-utilisateur sexe',
                    'type'    => 'choice',
                    'multiple' => false,
                    'expanded' => false,
                    'choices' => [
                        '0' => 'admin::compte-utilisateur:sexe: mademoiselle',
                        '1' => 'admin::compte-utilisateur:sexe: madame',
                        '2' => 'admin::compte-utilisateur:sexe: monsieur',
                    ]
                ],
                'firstname' => [
                    'label' => 'admin::compte-utilisateur prenom',
                    'type' => 'text',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ],
                'lastname' => [
                    'label' => 'admin::compte-utilisateur nom',
                    'type' => 'text',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ],
                'address' => [
                    'label' => 'admin::compte-utilisateur adresse',
                    'type' => 'textarea',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ],
                'zipcode' => [
                    'label' => 'admin::compte-utilisateur code postal',
                    'type' => 'text',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ],
                'geonameid' => [
                    'label' => 'admin::compte-utilisateur ville',
                    'type' => new \Alchemy\Phrasea\Form\Type\GeonameType(),
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ],
                'position' => [
                    'label' => 'admin::compte-utilisateur poste',
                    'type' => 'text',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ],
                'company' => [
                    'label' => 'admin::compte-utilisateur societe',
                    'type' => 'text',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ],
                'job' => [
                    'label' => 'admin::compte-utilisateur activite',
                    'type' => 'text',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ],
                'tel' => [
                    'label' => 'admin::compte-utilisateur tel',
                    'type' => 'text',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ],
                'fax' => [
                    'label' => 'admin::compte-utilisateur fax',
                    'type' => 'text',
                    'constraints' => [
                        new Assert\NotBlank(),
                    ]
                ],
            ];
        });
    }

    public function boot(Application $app)
    {
    }
}
