<?php

/*
 * This file is part of the FOSRest package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Util;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

use Negotiation\FormatNegotiator as BaseFormatNegotiator;

class FormatNegotiator implements FormatNegotiatorInterface
{
    /**
     * @var array
     */
    private $map = array();

    public function __construct()
    {
        $this->formatNegotiator = new BaseFormatNegotiator();
    }

    /**
     * @param RequestMatcherInterface $requestMatcher A RequestMatcherInterface instance
     * @param array                   $options        An array of options
     */
    public function add(RequestMatcherInterface $requestMatcher, array $options = array())
    {
        $this->map[] = array($requestMatcher, $options);
    }

    /**
     * Return the cache options for the current request
     *
     * @param Request $request
     * @return array of settings
     */
    protected function getOptions(Request $request)
    {
        foreach ($this->map as $elements) {
            if (null === $elements[0] || $elements[0]->matches($request)) {
                return $elements[1];
            }
        }

        return array();
    }

    /**
     * Detect the request format based on the priorities and the Accept header
     *
     * Note: Request "_format" parameter is considered the preferred Accept header
     *
     * @param   Request         $request          The request
     * @return  void|string                       The format string
     */
    public function getBestFormat(Request $request)
    {
        $options = $this->getOptions($request);
        if (empty($options['priorities'])) {
            return isset($options['fallback_format']) ? $options['fallback_format'] : null;
        }

        $acceptHeader = $request->headers->get('Accept');

        if ($options['prefer_extension']) {
            $extension = $request->get('_format');
            if (null !== $extension && $request->getMimeType($extension)) {
                if ($acceptHeader) {
                    $acceptHeader.= ',';
                }

                $acceptHeader.= $request->getMimeType($extension).'; q='.$options['prefer_extension'];
            }
        }

        return $this->formatNegotiator->getBestFormat($acceptHeader, $options['priorities']);
    }

    /**
     * Register a new format with its mime types.
     *
     * @param string  $format
     * @param array   $mimeTypes
     * @param boolean $override
     */
    public function registerFormat($format, array $mimeTypes, $override = false)
    {
        $this->formatNegotiator->registerFormat($format, $mimeTypes, $override);
    }

    /**
     * Returns the format for a given mime type, or null
     * if not found.
     *
     * @param string $mimeType
     *
     * @return string|null
     */
    public function getFormat($mimeType)
    {
        $this->formatNegotiator->getFormat($mimeType);
    }
}