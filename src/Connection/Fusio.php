<?php
/*
 * Fusio
 * A web-application to create dynamically RESTful APIs
 *
 * Copyright (C) 2015-2023 Christoph Kappestein <christoph.kappestein@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Fusio\Adapter\Fusio\Connection;

use Fusio\Adapter\Fusio\Introspection\Introspector;
use Fusio\Engine\Connection\IntrospectableInterface;
use Fusio\Engine\Connection\Introspection\IntrospectorInterface;
use Fusio\Engine\Connection\PingableInterface;
use Fusio\Engine\ConnectionInterface;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Sdk\Backend\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sdkgen\Client\Credentials\ClientCredentials;

/**
 * Fusio
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org/
 */
class Fusio implements ConnectionInterface, PingableInterface, IntrospectableInterface
{
    public function getName(): string
    {
        return 'Fusio';
    }

    public function getConnection(ParametersInterface $config): Client
    {
        $baseUrl = rtrim($config->get('url'), '/');
        $credentials = new ClientCredentials($config->get('client_id'), $config->get('client_secret'), $baseUrl . '/authorization/token', '');
        $client = new Client($baseUrl, $credentials);

        return $client;
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newInput('url', 'Url', 'text', 'Url of the remote Fusio instance'));
        $builder->add($elementFactory->newInput('client_id', 'Client-ID', 'text', 'The client id'));
        $builder->add($elementFactory->newInput('client_secret', 'Client-Secret', 'password', 'The client secret'));
    }

    public function ping(mixed $connection): bool
    {
        try {
            $connection->getBackendLog()->backendActionLogGetAll();
            return true;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    public function getIntrospector(mixed $connection): IntrospectorInterface
    {
        return new Introspector($connection);
    }
}
