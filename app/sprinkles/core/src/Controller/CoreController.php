<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */ 
namespace UserFrosting\Sprinkle\Core\Controller;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\NotFoundException as NotFoundException;
use UserFrosting\Sprinkle\Core\Util\MimeType;
    
/**
 * CoreController Class
 *
 * Implements some common sitewide routes.
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/navigating/#structure
 */
class CoreController
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;
    
    /**
     * Constructor.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    /**
     * Renders the default home page for UserFrosting.
     *
     * By default, this is the page that non-authenticated users will first see when they navigate to your website's root.
     * Request type: GET
     */
    public function pageIndex($request, $response, $args)
    {
        return $this->ci->view->render($response, 'pages/index.html.twig');
    }    
    
    /**
     * Renders a sample "about" page for UserFrosting.
     *
     * Request type: GET
     */
    public function pageAbout($request, $response, $args)
    {
        return $this->ci->view->render($response, 'pages/about.html.twig');
    }      
    
    /**
     * Render the alert stream as a JSON object.
     *
     * The alert stream contains messages which have been generated by calls to `MessageStream::addMessage` and `MessageStream::addMessageTranslated`.
     * Request type: GET
     */
    public function jsonAlerts($request, $response, $args)
    {
        return $response->withJson($this->ci->alerts->getAndClearMessages());
    }
    
    /**
     * Handle all requests for raw assets.
     * Request type: GET     
     */    
    public function getAsset($request, $response, $args)
    {
        $url = $args['url'];
        
        $config = $this->ci->config;
        
        // Remove any query string
        $url = preg_replace('/\?.*/', '', $url);
        
        // Find file
        $abspath = "assets://" . $url;
        
        // Return 404 if file does not exist
        if (!file_exists($abspath)) {
            throw new NotFoundException($request, $response);
        }
        
        $content = file_get_contents($abspath);
        $type = MimeType::detectByFilename($url);
        $length = filesize($abspath);
        
        return $response
            ->withHeader('Content-Type', $type)
            ->withHeader('Content-Length', $length)
            ->write($content);    
    }
}