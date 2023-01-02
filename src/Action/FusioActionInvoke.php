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

namespace Fusio\Adapter\Fusio\Action;

use Fusio\Engine\ActionAbstract;
use Fusio\Engine\ContextInterface;
use Fusio\Engine\Exception\ConfigurationException;
use Fusio\Engine\Form\BuilderInterface;
use Fusio\Engine\Form\ElementFactoryInterface;
use Fusio\Engine\ParametersInterface;
use Fusio\Engine\Request\HttpRequest;
use Fusio\Engine\RequestInterface;
use Fusio\Sdk\Backend\ActionExecuteRequest;
use Fusio\Sdk\Backend\ActionExecuteRequestBody;
use Fusio\Sdk\Backend\ActionExecuteResponse;
use Fusio\Sdk\Backend\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\TransferException;
use PSX\Http\Environment\HttpResponseInterface;
use PSX\Http\Exception as StatusCode;

/**
 * Action which invokes an action of a remote Fusio instance
 *
 * @author  Christoph Kappestein <christoph.kappestein@gmail.com>
 * @license http://www.gnu.org/licenses/agpl-3.0
 * @link    https://www.fusio-project.org/
 */
class FusioActionInvoke extends ActionAbstract
{
    public function getName(): string
    {
        return 'Fusio-Action-Invoke';
    }

    public function handle(RequestInterface $request, ParametersInterface $configuration, ContextInterface $context): HttpResponseInterface
    {
        $client = $this->connector->getConnection($configuration->get('connection'));
        if (!$client instanceof Client) {
            throw new ConfigurationException('Given connection must be a Fusio connection');
        }

        $actionName = $configuration->get('action');
        if (empty($actionName)) {
            throw new ConfigurationException('No action provided');
        }

        try {
            $response = $client->getBackendActionExecuteByActionId('~' . $actionName)->backendActionActionExecute($this->buildRequest($request));
        } catch (ServerException $e) {
            throw new StatusCode\ServerErrorException($e->getMessage(), $e);
        } catch (ClientException $e) {
            throw new StatusCode\ClientErrorException($e->getMessage(), $e);
        } catch (TransferException $e) {
            throw new StatusCode\InternalServerErrorException('Could not invoke action', $e);
        }

        return $this->response->build($response->getStatusCode(), $this->buildHeaders($response), $response->getBody());
    }

    public function configure(BuilderInterface $builder, ElementFactoryInterface $elementFactory): void
    {
        $builder->add($elementFactory->newConnection('connection', 'Connection', 'The Fusio connection which should be used'));
        $builder->add($elementFactory->newInput('action', 'Action', 'text', 'Name of the remote action'));
    }

    private function buildRequest(RequestInterface $request): ActionExecuteRequest
    {
        $data = new ActionExecuteRequest();

        if ($request instanceof HttpRequest) {
            $data->setMethod($request->getMethod());
            $data->setHeaders($request->getHeaders());
            $data->setUriFragments($request->getUriFragments());
            $data->setParameters($request->getParameters());
            $data->setBody(ActionExecuteRequestBody::from($request->getBody()));
        }

        return $data;
    }

    private function buildHeaders(ActionExecuteResponse $response): array
    {
        $result = [];
        $headers = $response->getHeaders();
        if (!empty($headers)) {
            foreach ($headers as $key => $value) {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
