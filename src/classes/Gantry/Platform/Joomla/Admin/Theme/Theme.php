<?php
namespace Gantry\Admin\Theme;

use Gantry\Component\Filesystem\Folder;
use Gantry\Component\Filesystem\Streams;
use Gantry\Component\Twig\TwigExtension;
use Gantry\Framework\Base\Theme as BaseTheme;
use RocketTheme\Toolbox\StreamWrapper\Stream;
use RocketTheme\Toolbox\StreamWrapper\ReadOnlyStream;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;

class Theme extends BaseTheme
{
    public function __construct($path, $name = '')
    {
        $gantry = \Gantry\Framework\Gantry::instance();

        $relpath = Folder::getRelativePath($path);

        // Initialize admin streams.
        $gantry['platform']->set(
            'streams.gantry-admin.prefixes', [
                ''        => [$relpath, $relpath . '/common'],
                'assets/' => [$relpath, $relpath . '/common']
            ]
        );

        parent::__construct($path, $name);

        $this->url = \JUri::root(true) . '/templates/' . $this->name;
        $this->boot();
    }


    protected function boot()
    {
        $gantry = \Gantry\Framework\Gantry::instance();

        /** @var UniformResourceLocator $locator */
        $locator = $gantry['locator'];
        $locator->addPath('gantry-admin', '', 'theme://admin');

        /** @var Streams $streams */
        $streams = $gantry['streams'];
        $streams->register();
    }

    public function add_to_context(array $context)
    {
        $context = parent::add_to_context($context);

        return $context;
    }

    public function add_to_twig(\Twig_Environment $twig, \Twig_Loader_Filesystem $loader = null)
    {
        $gantry = \Gantry\Framework\Gantry::instance();

        /** @var UniformResourceLocator $locator */
        $locator = $gantry['locator'];

        if (!$loader) {
            $loader = $twig->getLoader();
        }
        $loader->setPaths($locator->findResources('gantry-admin://templates'), 'gantry-admin');

        $twig->addExtension(new TwigExtension);
        return $twig;
    }

    public function render($file, array $context = array())
    {
        $loader = new \Twig_Loader_Filesystem($this->path . '/templates');

        $params = array(
            'cache' => JPATH_CACHE . '/gantry5admin/twig',
            'debug' => true,
            'auto_reload' => true,
            'autoescape' => false
        );

        $twig = new \Twig_Environment($loader, $params);

        $this->add_to_twig($twig);

        // Include Gantry specific things to the context.
        $context = $this->add_to_context($context);

        // Add JavaScript Frameworks
        \JHtml::_('bootstrap.framework');

        return $twig->render($file, $context);
    }
}
