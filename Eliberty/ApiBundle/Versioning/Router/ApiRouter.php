<?php

namespace Eliberty\ApiBundle\Versioning\Router;

use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\AcceptHeader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * Class ApiRouter.
 */
class ApiRouter extends Router implements RequestMatcherInterface
{
    /**
     * @var RequestStack
     */
    protected $requestStack;
    /**
     * @var string
     */
    private $acceptHeader;

    /**
     * @param ContainerInterface $container
     * @param mixed              $resource
     * @param array              $options
     * @param RequestContext $context
     */
    public function __construct(ContainerInterface $container, $resource, array $options = array(), RequestContext $context = null)
    {
        parent::__construct($container, $resource, $options, $context);
        //http://urthen.github.io/2013/05/16/ways-to-version-your-api-part-2/
        $this->acceptHeader = "/application\/vnd.eliberty.api.+json/";
        $this->requestStack = $container->get('request_stack');
    }

    /**
     * Tries to match a request with a set of routes.
     *
     * If the matcher can not find information, it must throw one of the exceptions documented
     * below.
     *
     * @param Request $request The request to match
     *
     * @return array An array of parameters
     *
     * @throws ResourceNotFoundException If no matching resource could be found
     * @throws MethodNotAllowedException If a matching resource was found but the request method is not allowed
     */
    public function matchRequest(Request $request)
    {
        $version = "v1";

        $request = $this->requestStack->getCurrentRequest();
        $acceptHeader = AcceptHeader::fromString($request->headers->get('Accept'))->all();
        foreach ($acceptHeader as $acceptHeaderItem) {
            if ($acceptHeaderItem->hasAttribute('version')) {
                $version = $acceptHeaderItem->getAttribute('version');
                break;
            }
        }

        /*
         * @TODO check if always necessary
         */
        $context = $this->getContext();
        $context->setMethod($request->getMethod());
        $context->setApiVersion($version);

        return $this->match($request->getPathInfo());

        //return parent::matchRequest($request);
    }
}
