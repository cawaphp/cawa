<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types = 1);

namespace Cawa\DependencyInjection;

use Cawa\Cache\Cache;
use Cawa\Core\App;
use Cawa\Db\AbstractDatabase;
use Cawa\Db\TransactionDatabase;
use Cawa\Http\Client;
use Cawa\Uri\Uri;

class Container
{
    /**
     * @var array
     */
    private $container = [];

    /**
     * @param string $name
     *
     * @return TransactionDatabase
     */
    public function getDb(string $name = null) : TransactionDatabase
    {
        $containerName = 'db.' . ($name ?: 'default');

        if (isset($this->container[$containerName])) {
            return $this->container[$containerName];
        }

        $config = App::config()->get('db/' . ($name ?: 'default'));
        $this->container[$containerName] = AbstractDatabase::create($config);

        return $this->container[$containerName];
    }

    /**
     * @param string $name
     *
     * @return Cache
     */
    public function getCache(string $name = null) : Cache
    {
        $containerName = 'cache.' . ($name ?: 'default');

        if (isset($this->container[$containerName])) {
            return $this->container[$containerName];
        }

        $config = App::config()->get('cache/' . ($name ?: 'default'));
        $this->container[$containerName] = Cache::create($config);

        return $this->container[$containerName];
    }

    /**
     * @param string $name
     *
     * @return Client
     */
    public function getHttpClient(string $name) : Client
    {
        $containerName = 'httpclient.' . $name;
        if (isset($this->container[$containerName])) {
            return $this->container[$containerName];
        }

        $config = App::config()->get('httpclient/' . $name);

        if (is_callable($config)) {
            $return = $config();
        } else {
            $return = new Client();
            $return->setBaseUri($config);
        }

        $this->container[$containerName] = $return;

        return $this->container[$containerName];
    }

    /**
     * @param string $name
     *
     * @return \Swift_Mailer
     */
    public function getEmailMailer(string $name = null) : \Swift_Mailer
    {
        $containerName = 'email.' . ($name ?: 'default');

        if (isset($this->container[$containerName])) {
            return $this->container[$containerName];
        }

        $config = App::config()->getIfExists('email/' . ($name ?: 'default'));
        if (!$config) {
            $transport = \Swift_MailTransport::newInstance();
            $return = \Swift_Mailer::newInstance($transport);
        } elseif (is_callable($config)) {
            $return = $config();
        } else {
            $uri = new Uri($config);
            switch ($uri->getScheme()) {
                case 'smtp':
                    $transport = \Swift_SmtpTransport::newInstance($uri->getHost(), $uri->getPort());

                    if ($uri->getUser()) {
                        $transport->setUsername($uri->getUser());
                    }

                    if ($uri->getPassword()) {
                        $transport->setPassword($uri->getPassword());
                    }

                    if ($uri->getQuery('auth')) {
                        $transport->setAuthMode($uri->getQuery('auth'));
                    }

                    if ($uri->getQuery('encryption')) {
                        $transport->setEncryption($uri->getQuery('encryption'));
                    }
                    break;

                default:
                    throw new \InvalidArgumentException(
                        sprintf("Undefined email mailer type '%s'", $uri->getScheme())
                    );
                    break;
            }
            $return = \Swift_Mailer::newInstance($transport);
        }

        $this->container[$containerName] = $return;

        return $this->container[$containerName];
    }
}
