<?php

namespace koeshiro\Routes;

use Closure;
use koeshiro\Routes\Interfaces\RouterInterface;
use koeshiro\Routes\Exceptions\PageNotFound;
use Nyholm\Psr7\Factory\Psr17Factory as PsrFactory;
use Psr;

/**
 *
 * @author rustam
 *        
 */
class Router implements RouterInterface {

    protected $Routs = [];
    public $AllowedMethods = [
        'head' => 'head',
        'path' => 'path',
        'post' => 'post',
        'get' => 'get',
        'track' => 'track',
        'delete' => 'delete',
        'put' => 'put',
        'connect' => 'connect',
        'options' => 'options'
    ];

    public function map(array $Methods, string $Rout, Closure $Callback) {
        $MethodsPrepeared = [];
        foreach ($Methods as $Method) {
            $cache = strtolower($Method);
            if (in_array($cache, self::$AllowedMethods)) {
                $MethodsPrepeared[] = $cache;
            } else {
                throw new \RuntimeException("Not allowed method ($cache)");
            }
        }
        $this->Routs[] = [
            'METHODS' => $MethodsPrepeared,
            'ROUT' => $Rout,
            'CALLBACK' => $Callback
        ];
    }

    public function any(string $Rout, Closure $Callback) {
        $this->map($this->$AllowedMethods, $Rout, $Callback);
    }

    public function head(string $Rout, Closure $Callback) {
        $this->map(['head'], $Rout, $Callback);
    }

    public function path(string $Rout, Closure $Callback) {
        $this->map(['path'], $Rout, $Callback);
    }

    public function post(string $Rout, Closure $Callback) {
        $this->map(['post'], $Rout, $Callback);
    }

    public function get(string $Rout, Closure $Callback) {
        $this->map(['get'], $Rout, $Callback);
    }

    public function track(string $Rout, Closure $Callback) {
        $this->map(['track'], $Rout, $Callback);
    }

    public function delete(string $Rout, Closure $Callback) {
        $this->map(['delete'], $Rout, $Callback);
    }

    public function put(string $Rout, Closure $Callback) {
        $this->map(['put'], $Rout, $Callback);
    }

    public function connect(string $Rout, Closure $Callback) {
        $this->map(['connect'], $Rout, $Callback);
    }

    public function options(string $Rout, Closure $Callback) {
        $this->map(['options'], $Rout, $Callback);
    }
    
    protected function test(string $Rout, string $Url): bool {
        try {
            $ClearRout = preg_replace("/\(:[a-zA-Z0-9_]+\)/", '', $Rout);
            return preg_match("/$ClearRout/", $Url);
        } catch (\Exception $E) {
            throw new \Exception($E->getMessage() . "\n on line /$Url/");
        }
    }

    protected function getParameters(string $Rout, string $Url): array {
        if (preg_match("/\(\:[a-zA-Z0-9_]+\)/", $Rout)) {
            /**
             * Raw section name in array 
             * @see https://www.php.net/manual/en/function.preg-match-all.php
             * @var array $RawSectionName
             */
            $RawSectionName = [];
            /**
             * Name of rout named (templated) section
             * @var string $RoutValueName
             */
            $SectionName = '';
            /**
             * Value of rout named (templated) section
             * @var string $RoutValueName
             */
            $SectionValue = '';
            /**
             * Split url by sections
             * @var array $URLPathArray
             */
            $URLPathArray = explode('/', $Url);
            /**
             * Split rout by sections
             * @var array $RoutPathArray
             */
            $RoutPathArray = explode('/', $Rout);
            /**
             * results
             * @var array $Result
             */
            $Result = [];
            foreach ($RoutPathArray as $index => $section) {
                //if section have :name
                if (preg_match("/\(\:[a-zA-Z0-9_]+\)/", $section)) {
                    //getting section name
                    preg_match_all("/\:([a-zA-Z0-9_]+)/", $section, $RawSectionName);
                    $SectionName = $RawSectionName[1][0];
                    //gettin section value
                    $SectionValue = $URLPathArray[$index];
                    if (array_key_exists($SectionName, $Result) && !is_array($Result[$SectionName])) {
                        $Result[$SectionName] = [$Result[$SectionName], $SectionValue];
                    } else if (array_key_exists($SectionName, $Result)) {
                        $Result[$SectionName][] = $SectionValue;
                    } else {
                        $Result[$SectionName] = $SectionValue;
                    }
                }
            } return $Result;
        } else {
            return [];
        }
    }
    /**
     * Method for create Request
     * @param \Psr\Http\Message\RequestFactoryInterface $RequestFactory
     * @param \Psr\Http\Message\StreamFactoryInterface $StreamFactory
     * @param \Psr\Http\Message\UploadedFileFactoryInterface $UploadedFileFactory
     * @param array $SERVER
     * @param array $COOKIE
     * @param array $GET
     * @param array $POST
     * @param array $FILE
     * @return \Psr\Http\Message\RequestInterface
     * @throws \RuntimeException
     */
    public static function createRequest(
            \Psr\Http\Message\RequestFactoryInterface $RequestFactory,
            \Psr\Http\Message\StreamFactoryInterface $StreamFactory,
            \Psr\Http\Message\UploadedFileFactoryInterface $UploadedFileFactory,
            array $SERVER = [],
            array $COOKIE = [],
            array $GET = [],
            array $POST = [],
            array $FILE = []
    ):\Psr\Http\Message\RequestInterface {
        /**
         * http url target parameter
         * @var string $RequestUrl
         */
        $RequestUrl = '';
        if (array_key_exists('REQUEST_URI', $SERVER) && is_string($SERVER['REQUEST_URI'])) {
            $RequestUrl = explode('?', $SERVER['REQUEST_URI'])[0];
        } else {
            throw new \RuntimeException(
                    "Url is nod defined.
				\$_SERVER[REQUEST_URI] / \$Url is not string"
            );
        }
        /**
         * Http method
         * @var string $RequestMethod
         */
        $RequestMethod = $SERVER['REQUEST_METHOD'];
        /**
         * Request
         * @var \Psr\Http\Message\RequestInterface $Request
         */
        $Request = $PsrFactory->createServerRequest($RequestMethod, $RequestUrl, $SERVER);
        $Request = $Request->withCookieParams($COOKIE);
        if (strpos(filter_input(INPUT_SERVER, "CONTENT_TYPE", FILTER_SANITIZE_STRING), 'json') !== false) {
            $Request = $Request->withParsedBody(json_decode(file_get_contents("php://input"), true));
        } else {
            $Request = $Request->withParsedBody($POST);
        }
        $Request = $Request->withQueryParams($GET);
        $headers = [];
        foreach ($SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        foreach ($headers as $key => $value) {
            $Request = $Request->withHeader($key, $value);
        }
        $NormalizedFiles = [];
        foreach ($FILES as $name => $fileArray) {
            if (is_array($fileArray['name'])) {
                foreach ($fileArray as $attrib => $list) {
                    foreach ($list as $index => $value) {
                        $NormalizedFiles[$name][$index][$attrib] = $value;
                    }
                }
            } else {
                $NormalizedFiles[$name][] = $fileArray;
            }
        }
        $prepearedFiles = [];
        foreach ($NormalizedFiles as $FirstLevelKey => $Files) {
            foreach ($Files as $SecondLevelKey => $File) {
                $prepearedFiles[$FirstLevelKey][$SecondLevelKey] = $UploadedFileFactory->createUploadedFile(
                        $StreamFactory->createStreamFromFile($File['tmp_name']),
                        $File['size'],
                        $File['error'],
                        $File['name'],
                        $File['type']
                );
            }
        }
        $Request = $Request->withUploadedFiles($prepearedFiles);
        return $Request;
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param array $Options
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \RuntimeException,PageNotFound
     */
    public function execute(\Psr\Http\Message\RequestInterface $request, array $Options): \Psr\Http\Message\ResponseInterface  {
        /**
         * @var \Psr\Http\Message\UriInterface Description
         */
        $url = $request->getUri();
        foreach ($this->Routs as $RoutSettings) {
            if ($this->test($RoutSettings['ROUT'],$url->getPath())  && in_array($RequestMethod, $request->getMethod())){
                $CallBack = $RoutSettings['CALLBACK'];
                $Parameters = $this->getParameters($RoutSettings['ROUT'], $RequestUrl);
                return $CallBack((clone $request), $Parameters, $Options);
            }
        }
        throw new PageNotFound();
    }

}
