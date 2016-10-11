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

use Cawa\Events\DispatcherFactory;
use Cawa\Events\TimerEvent;
use Cawa\Intl\TranslatorFactory;

class HtmlPage extends HtmlContainer
{
    use TranslatorFactory;
    use AssetTrait;
    use DispatcherFactory;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct('<html>');

        $this->head = new HtmlContainer('<head>');
        $this->add($this->head);

        $this->footer = new Container();

        $this->body = new HtmlContainer('<body>');
        $this->add($this->body);
    }

    /**
     * @var HtmlContainer
     */
    private $head;

    /**
     * @return HtmlContainer
     */
    public function getHead() : HtmlContainer
    {
        return $this->head;
    }

    /**
     * @var HtmlContainer
     */
    private $footer;

    /**
     * @return Container
     */
    public function getFooter() : Container
    {
        return $this->footer;
    }

    /**
     * @var HtmlContainer
     */
    private $body;

    /**
     * @return HtmlContainer
     */
    public function getBody() : HtmlContainer
    {
        return $this->body;
    }

    /**
     * @var HtmlElement
     */
    private $headTitle;

    /**
     * @return bool
     */
    public function hasHeadTitle() : bool
    {
        return !is_null($this->headTitle);
    }

    /**
     * @return string|null
     */
    public function getHeadTitle()
    {
        return $this->headTitle ? $this->headTitle->getContent() : null;
    }

    /**
     * @param string $title
     *
     * @return $this|self
     */
    public function setHeadTitle(string $title) : self
    {
        if (!$this->headTitle) {
            $this->headTitle = new HtmlElement('<title>');
            $this->head->addFirst($this->headTitle);
        }
        $this->headTitle->setContent($title);

        return $this;
    }

    /**
     * @var HtmlElement
     */
    private $headDescription;

    /**
     * @return string|null
     */
    public function getHeadDescription()
    {
        return $this->headDescription ? $this->headDescription->getContent() : null;
    }

    /**
     * @param string $description
     *
     * @return $this|self
     */
    public function setHeadDescription(string $description) : self
    {
        if (!$this->headDescription) {
            $this->headDescription = new HtmlElement('<meta>');
            $this->headDescription->addAttribute('name', 'description');
            $this->head->add($this->headDescription);
        }
        $this->headDescription->addAttribute('content', $description);

        return $this;
    }

    /**
     * Add Css file inclusion
     *
     * @param string $css
     * @param array $attributes
     *
     * @return $this|self
     */
    public function addCss(string $css, array $attributes = []) : self
    {
        if (substr($css, -4) == '.css' ||
            substr($css, 0, 2) == '//' ||
            substr($css, 0, 4) == 'http'
        ) {
            list($path, $hash) = $this->getAssetData($css);

            $meta = new HtmlElement('<link />');

            if ($hash) {
                $meta->addAttribute('name', str_replace(['.css', '.', '/', ':'], ['', '_', '_', '_'], $css));
            }

            $meta->addAttributes([
                'type' => 'text/css',
                'rel' => 'stylesheet',
                'href' => $path
            ]);
        } else {
            $meta = new HtmlElement('<style>');
            $meta->addAttributes($attributes);
            $meta->addAttribute('type', 'text/css')
                ->setContent($css);
        }

        $this->head->add($meta);

        return $this;
    }

    /**
     * Add Css file inclusion
     *
     * @param string $javascript
     * @param array $attributes
     * @param bool $footer
     *
     * @return $this|self
     */
    public function addJs(string $javascript, array $attributes = [], bool $footer = true) : self
    {
        $meta = new HtmlElement('<script>');
        $meta->addAttribute('type', 'text/javascript');
        $meta->addAttributes($attributes);

        if (substr($javascript, -3) == '.js' ||
            substr($javascript, 0, 2) == '//' ||
            substr($javascript, 0, 4) == 'http'
        ) {
            list($path, $hash) = $this->getAssetData($javascript);
            if ($hash) {
                $meta->addAttribute('name', str_replace(['.js', '.', '/', ':'], ['', '_', '_', '_'], $javascript));
            }

            $meta->addAttribute('src', $path);
        } else {
            $meta->setContent($javascript);
        }

        if ($footer) {
            $this->footer->add($meta);
        } else {
            $this->head->add($meta);
        }

        // add a preload header
        if ($meta->getAttribute('src') && $footer) {
            $preload = new HtmlElement('<link>');
            $preload->addAttributes([
                'as' => 'script',
                'href' => $meta->getAttribute('src'),
                'rel' => 'preload'
            ]);
            $this->head->add($preload);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function render()
    {
        $timerEvent = new TimerEvent('router.htmlPage', [
            'elements' => sizeof($this->elements),

        ]);

        $out = '<!DOCTYPE html lang="' . self::locale() . '">' . "\n";

        // default seo
        if (!$this->headTitle && $title = self::trans('seo.default/title')) {
            $this->setHeadTitle($title);
        }

        if (!$this->headDescription && $description = self::trans('seo.default/description')) {
            $this->setHeadDescription($description);
        }

        // add mandatory headers
        $language = new HtmlElement('<meta />');
        $language->addAttributes([
            'http-equiv' => 'Content-Language',
            'content' => self::locale()
        ]);
        $this->head->addFirst($language);

        $charset = new HtmlElement('<meta />');
        $charset->addAttribute('charset', 'utf-8');
        $this->head->addFirst($charset);

        $content = $this->body->getContent();
        if ($content) {
            $this->body->setContent($content . $this->footer->render());
        }

        $parent = parent::render();
        $out .= $parent;

        $timerEvent->addData(['size' => $this->getContent() ? strlen($this->getContent()) : strlen($parent)]);
        self::emit($timerEvent);

        return $out;
    }
}
