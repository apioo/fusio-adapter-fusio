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

namespace Fusio\Adapter\Fusio\Introspection;

use Fusio\Engine\Connection\Introspection\Entity;
use Fusio\Engine\Connection\Introspection\IntrospectorInterface;
use Fusio\Engine\Connection\Introspection\Row;
use Fusio\Sdk\Backend\Action;
use Fusio\Sdk\Backend\Client;

/**
 * Introspector
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org/
 */
class Introspector implements IntrospectorInterface
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function getEntities(): array
    {
        $actions = $this->client->getBackendAction()->backendActionActionGetAll()?->getEntry();
        if (empty($actions) || !is_array($actions)) {
            return [];
        }

        $names = [];
        foreach ($actions as $action) {
            /** @var Action $action */
            $names[] = $action->getName();
        }

        return $names;
    }

    public function getEntity(string $entityName): Entity
    {
        $action = $this->client->getBackendActionByActionId('~' . $entityName)->backendActionActionGet();

        $values = [
            'Class' => $action->getClass(),
            'Engine' => $action->getEngine(),
            'Config' => \json_encode($action->getConfig(), \JSON_PRETTY_PRINT),
        ];

        $entity = new Entity($action->getName(), ['Key', 'Value']);
        foreach ($values as $key => $value) {
            $entity->addRow(new Row([
                $key,
                $value,
            ]));
        }

        return $entity;
    }
}
