<?php
namespace Aspi\Framework\Routing;
use Aspi\Framework\Routing\RouteGenerator\NameInflector\PostfixInflector;
use Aspi\Framework\Routing\RouteGenerator\NameInflector\RouteNameInflectorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\RequestContext;
class Router implements RouterInterface
{
    /**
     * @var UrlMatcher
     */
    protected $router;

    /**
     * @var UrlGenerator 
     */
    protected $generator;
    
    /**
     * The locale to use when neither the parameters nor the request context
     * indicate the locale to use.
     *
     * @var string
     */
    protected $defaultLocale;
    /**
     * @var RouteNameInflectorInterface
     */
    private $routeNameInflector;
    /**
     * Constructor
     *
     * @param \Symfony\Component\Routing\Matcher\UrlMatcher  $router
     * @param string                                       $defaultLocale
     */
    public function __construct(\Symfony\Component\Routing\Matcher\UrlMatcher $router,\Symfony\Component\Routing\Generator\UrlGenerator $generator, $defaultLocale = null, RouteNameInflectorInterface $routeNameInflector = null)
    {
        $this->router = $router;
        $this->generator = $generator;
        $this->defaultLocale = $defaultLocale;
        $this->routeNameInflector = $routeNameInflector ?: new PostfixInflector();
    }
    /**
     * Generates a URL from the given parameters.
     *
     * @param string   $name       The name of the route
     * @param array    $parameters An array of parameters
     * @param bool|int $referenceType The type of reference to be generated (one of the constants)
     *
     * @return string The generated URL
     *
     * @throws \InvalidArgumentException When the route doesn't exists
     */
    public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
    {
        if (isset($parameters['locale'])) {
            $locale = $this->getLocale($parameters);
            if (isset($parameters['locale'])) {
                unset($parameters['locale']);
            }
            if (null === $locale) {
                throw new MissingMandatoryParametersException('The locale must be available.');
            }
            return $this->generateI18n($name, $locale, $parameters, $referenceType);
        }
        try {
            return $this->generator->generate($name, $parameters, $referenceType);
        } catch (RouteNotFoundException $e) {
            $locale = $this->getLocale($parameters);
            if (null !== $locale) {
                return $this->generateI18n($name, $locale, $parameters, $referenceType);
            }
            throw $e;
        }
    }
     /**
     * {@inheritDoc}
     */
    public function match($pathinfo)
    {
        $match = $this->router->match($pathinfo);
        return $match;
    }
    public function getRouteCollection()
    {
        return $this->router->getRouteCollection();
    }
    public function setContext(RequestContext $context)
    {
        $this->router->setContext($context);
    }
    public function getContext()
    {
        return $this->router->getContext();
    }
    /**
     * Overwrite the locale to be used by default if the current locale could
     * not be found when building the route
     *
     * @param string $locale
     */
    public function setDefaultLocale($locale)
    {
        $this->defaultLocale = $locale;
    }
    /**
     * Generates a I18N URL from the given parameter
     *
     * @param string   $name       The name of the I18N route
     * @param string   $locale     The locale of the I18N route
     * @param array    $parameters An array of parameters
     * @param bool|int $referenceType The type of reference to be generated (one of the constants)
     *
     * @return string The generated URL
     *
     * @throws RouteNotFoundException When the route doesn't exists
     */
    protected function generateI18n($name, $locale, $parameters, $referenceType = self::ABSOLUTE_PATH)
    {
        try {
            return $this->generator->generate(
                $this->routeNameInflector->inflect($name, $locale),
                $parameters,
                $referenceType
            );
        } catch (RouteNotFoundException $e) {
            throw new RouteNotFoundException(sprintf('I18nRoute "%s" (%s) does not exist.', $name, $locale));
        }
    }
    /**
     * Determine the locale to be used with this request
     *
     * @param array $parameters the parameters determined by the route
     *
     * @return string
     */
    protected function getLocale($parameters)
    {
        if (isset($parameters['locale'])) {
            return $parameters['locale'];
        }
        if ($this->getContext()->hasParameter('_locale')) {
            return $this->getContext()->getParameter('_locale');
        }
        return $this->defaultLocale;
    }
}