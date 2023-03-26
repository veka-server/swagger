<?php

namespace VekaServer\Swagger;

use \Nyholm\Psr7\Response;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Server\RequestHandlerInterface;

class Swagger implements MiddlewareInterface
{

    public function __construct() {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) :ResponseInterface
    {

        $rooter = new \VekaServer\Rooter\Rooter();

        // route de demo
        $rooter->get('/swagger/([a-zA-Z0-9_\-+ \.]+)',function($page){

            if (empty($page)) {
                $page = 'index.html';
            }

            $data = $this->getSwaggerFile($page,$contentType);

            $response = new Response();
            $response = $response->withStatus(200);
            $stream = $response->getBody();
            $stream->write($data ?? '');
            $response->withBody($stream);

            return $response->withHeader('Content-Type',$contentType);
        }) ;

        $rooter->set404( function() use($handler, $request){
            return $handler->handle($request);
        }
        );

        return $rooter->process($request, $handler);
    }

    protected function getSwaggerFile ($page, &$ct) :string
    {
        $public_directory = dirname(__DIR__,3).DIRECTORY_SEPARATOR.'swagger-api'.DIRECTORY_SEPARATOR.'swagger-ui'.DIRECTORY_SEPARATOR.'dist'.DIRECTORY_SEPARATOR;
        $file_path = $public_directory.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, explode('/', $page));
        $file_path = realpath($file_path);
        /** verifie que le fichier est toujours dans le dossier public */
        if(strpos( $file_path , $public_directory ) !== 0){
            return '';
        }

        if(!is_file($file_path))
            return '';

        $path_parts = pathinfo($file_path);

        switch($path_parts["extension"]){

            /** exclure les fichiers php */
            case 'php':
            case 'php1':
            case 'php2':
            case 'php3':
            case 'php4':
            case 'php5':
            case 'php6':
            case 'php7':
            case 'php8':
            case 'php9':
            case 'php10':
                return '';

            case 'html':
                $ct = 'text/html';
                break ;

            case 'css':
                $ct = 'text/css';
                break;

            case 'js':
                $ct = 'text/js';
                break;

            default:
                $ct = 'text/plain';
                break;

        }

        $data = file_get_contents($file_path);
        if($data === false){
            return '';
        }

        return $data;
    }

}
