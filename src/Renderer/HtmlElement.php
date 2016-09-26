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

namespace Cawa\Renderer;

class HtmlElement extends Element
{
    /**
     * @param string $tag
     * @param string $content
     */
    public function __construct(string $tag = null, string $content = null)
    {
        if (is_null($this->tag) && is_null($tag)) {
            throw new \InvalidArgumentException(sprintf('Missing tag on %s', get_class($this)));
        }

        if ($tag) {
            $this->tag = $tag;
        }

        parent::__construct($content);
    }

    /**
     * @var string
     */
    protected $tag;

    /**
     * @return string
     */
    public function getTag() : string
    {
        return $this->tag;
    }

    /**
     * @param string $tag
     *
     * @return $this|self
     */
    public function setTag(string $tag) : self
    {
        $this->tag = $tag;

        return $this;
    }

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @param string $name
     *
     * @return string|null
     */
    public function getAttribute(string $name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    /**
     * @param array $attributes
     *
     * @return $this|self
     */
    public function addAttributes(array $attributes) : self
    {
        foreach ($attributes as $key => $value) {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this|self
     */
    public function addAttribute(string $name, string $value) : self
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasAttribute(string $name) : bool
    {
        return isset($this->attributes[$name]) && $this->attributes[$name] ? true : false;
    }

    /**
     * @param string $name
     *
     * @return $this|self
     */
    public function removeAttribute(string $name) : self
    {
        if (isset($this->attributes[$name])) {
            unset($this->attributes[$name]);
        }

        return $this;
    }

    /**
     * @param string $value
     *
     * @return $this|self
     */
    public function setId(string $value) : self
    {
        return $this->addAttribute('id', $value);
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    /**
     * @return $this|self
     */
    public function generateId() : self
    {
        return $this->setId('uid-' . mt_rand());
    }

    /**
     * @return array
     */
    private function getStyles() : array
    {
        return $this->getAttribute('style') ? explode(';', $this->getAttribute('style')) : [];
    }

    /**
     * @param array|string $style
     *
     * @return $this|self
     */
    public function setStyle($style) : self
    {
        if (is_array($style) && sizeof($style)) {
            $finalStyle = implode(';', $style);
        } elseif (is_string($style)) {
            $finalStyle = $style;
        } else {
            $finalStyle = '';
        }

        return $this->addAttribute('style', trim($finalStyle, ';'));
    }

    /**
     * @param array|string $value
     *
     * @return $this|self
     */
    public function addStyle($value) : self
    {
        $currentStyles = $this->getStyles();
        $styles = array_unique(array_merge($currentStyles, (is_array($value) ? $value : explode(';', $value))));

        return $this->setStyle($styles);
    }

    /**
     * @return array
     */
    public function getClasses() : array
    {
        return $this->getAttribute('class') ? explode(' ', $this->getAttribute('class')) : [];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasClass(string $name) : bool
    {
        return $this->getAttribute('class') ? in_array($name, explode(' ', $this->getAttribute('class'))) : false;
    }

    /**
     * @param array|string $value
     *
     * @return $this|self
     */
    public function addClass($value) : self
    {
        $currentClasses = $this->getClasses();
        $classes = array_unique(array_merge($currentClasses, (is_array($value) ? $value : explode(' ', $value))));

        return $this->addAttribute('class', implode(' ', $classes));
    }

    /**
     * @param string $value
     *
     * @return $this|self
     */
    public function removeClass(string $value) : self
    {
        $currentClasses = $this->getClasses();
        $classes = array_diff($currentClasses, explode(' ', $value));

        return $this->addAttribute('class', implode(' ', $classes));
    }

    /**
     * @throws \LogicException
     *
     * @return string
     */
    public function render()
    {
        if (!$this->tag) {
            throw new \LogicException('Missing tag');
        }

        return $this->isRenderable() ? self::htmlTag($this->tag, $this->attributes, $this->content) : '';
    }

    /**
     * @return array
     */
    public function renderOuter() : array
    {
        if (!$this->tag) {
            throw new \LogicException('Missing tag');
        }

        $render = self::htmlTag($this->tag, $this->attributes, '[-INNER-]');

        return explode('[-INNER-]', $render);
    }

    /**
     * @param string $tag
     * @param array $attributes
     * @param string $innerHtml
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public static function htmlTag(string $tag, array $attributes = [], $innerHtml = null) : string
    {
        $invalid = $tag[0] != '<' || substr($tag, -1) != '>';

        if (substr($tag, -2) == '/>') {
            $control = trim(substr($tag, 0, -2));
        } else {
            $control = $tag;
        }

        $invalid = $invalid ? $invalid : strpos($control, ' ') !== false;

        if ($invalid) {
            throw new \InvalidArgumentException(sprintf("Please provide a valid tag format, '%s' given", $tag));
        }

        $autoClose = (substr($tag, -2) == '/>');

        $return = ($autoClose) ? trim(substr($tag, 0, -2)) : trim(substr($tag, 0, -1));
        $return .= ' ' . self::htmlAttribute($attributes);
        $return = trim($return);

        if ($autoClose) {
            $return .= ' />';
        } else {
            $return .= '>';
            if (!is_null($innerHtml)) {
                $return .= $innerHtml;
            }

            $return .= '</' . substr($tag, 1);
        }

        return $return;
    }

    /**
     * @param array $attributes
     *
     * @return string
     */
    public static function htmlAttribute(array $attributes = []) : string
    {
        $return = '';
        foreach ($attributes as $attribute => $value) {
            $json = false;
            if ($attribute == 'data-options') {
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                    $json = true;
                } elseif (is_string($value)) {
                    json_decode($value);
                    $json = json_last_error() == JSON_ERROR_NONE;
                }
            }

            $return .= $attribute;
            if (is_null($value)) {
                $return .= ' ';
            } else {
                $return .= '=' . (!$json ? '"' : "'");

                if (!is_array($value)) {
                    $return .= is_bool($value) ? ($value ? 'true' : 'false') : htmlentities($value);
                } else {
                    switch ($attribute) {
                        case 'style':
                            $return .= implode('; ', $value);
                            break;
                        default:
                            $return .= implode(' ', $value);
                            break;
                    }
                }
                $return .= (!$json ? '"' : "'") . ' ';
            }
        }

        return $return;
    }
}
