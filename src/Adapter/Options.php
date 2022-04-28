<?php
/**
 * @author Sebastian Pondo
 */

namespace Diagnolek\Http\Adapter;

use Diagnolek\Http\Exception\OptionException;

class Options extends \ArrayObject
{

    const OPT_VERIFY = 'verify';
    const OPT_JSON = 'json';
    const OPT_AUTH = 'authorization';
    const OPT_HEADER_RESPONSE = 'header_response';
    const OPT_USERPWD = 'userpwd';
    const OPT_USERAGENT = 'useragent';

    public $strict = false;

    protected function excluded(): array
    {
        return [
            self::OPT_VERIFY,
            self::OPT_HEADER_RESPONSE,
            self::OPT_JSON,
            self::OPT_AUTH,
        ];
    }

    protected function allowed(): array
    {
        return [
            self::OPT_USERPWD=>CURLOPT_USERPWD,
            self::OPT_USERAGENT=>CURLOPT_USERAGENT
        ];
    }

    public function isVerify(): bool
    {
        if (!$this->offsetExists(self::OPT_VERIFY)) {
            return true;
        }
        return $this->offsetGet(self::OPT_VERIFY) === true;
    }

    public function isJson(): bool
    {
        if (!$this->offsetExists(self::OPT_JSON)) {
            return false;
        }
        return $this->offsetGet(self::OPT_JSON) === true;
    }

    public function isHeaderResponse(): bool
    {
        if (!$this->offsetExists(self::OPT_HEADER_RESPONSE)) {
            return false;
        }
        return $this->offsetGet(self::OPT_HEADER_RESPONSE) === true;
    }

    public function getAuth():? string
    {
        return $this->offsetExists(self::OPT_AUTH) ? $this->offsetGet(self::OPT_AUTH) : null;
    }

    public function getArrayCopy(): array
    {
        $options = [];
        $arr = parent::getArrayCopy();
        $allowed = $this->allowed();
        foreach ($arr as $key => $val) {
            if ($this->strict && !isset($allowed[$key]) && !in_array($key, $this->excluded())) {
                throw new OptionException("$key not allowed parameter");
            } elseif (isset($allowed[$key])) {
                $options[$allowed[$key]] = $val;
            }
        }
        return $options;
    }

}