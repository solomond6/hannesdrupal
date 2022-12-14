<?php

namespace Drupal\Core\EventSubscriber;

use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Config\ConfigCrudEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

/**
 * High-performance 404 exception subscriber.
 *
 * This subscriber will return a minimalist 404 response for HTML requests
 * without running a full page theming operation.
 *
 * Fast 404s are configured using the system.performance configuration object.
 * There are the following options:
 * - system.performance:fast_404.exclude_paths: A regular expression to match
 *   paths to exclude, such as images generated by image styles, or
 *   dynamically-resized images. The default pattern provided below also
 *   excludes the private file system. If you need to add more paths, you can
 *   add '|path' to the expression.
 * - system.performance:fast_404.paths: A regular expression to match paths that
 *   should return a simple 404 page, rather than the fully themed 404 page. If
 *   you don't have any aliases ending in htm or html you can add '|s?html?' to
 *   the expression.
 * - system.performance:fast_404.html: The html to return for simple 404 pages.
 */
class Fast404ExceptionHtmlSubscriber extends HttpExceptionSubscriberBase {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The cache tags invalidator.
   *
   * @var \Drupal\Core\Cache\CacheTagsInvalidatorInterface
   */
  protected $cacheTagsInvalidator;

  /**
   * Constructs a new Fast404ExceptionHtmlSubscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Cache\CacheTagsInvalidatorInterface $cache_tags_invalidator
   *   The cache tags invalidator.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CacheTagsInvalidatorInterface $cache_tags_invalidator) {
    $this->configFactory = $config_factory;
    $this->cacheTagsInvalidator = $cache_tags_invalidator;
  }

  /**
   * {@inheritdoc}
   */
  protected static function getPriority() {
    // A very high priority so that it can take precedent over anything else,
    // and thus be fast.
    return 200;
  }

  /**
   * {@inheritdoc}
   */
  protected function getHandledFormats() {
    return ['html'];
  }

  /**
   * Handles a 404 error for HTML.
   *
   * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
   *   The event to process.
   */
  public function on404(ExceptionEvent $event) {
    $request = $event->getRequest();

    $config = $this->configFactory->get('system.performance');
    $exclude_paths = $config->get('fast_404.exclude_paths');
    if ($config->get('fast_404.enabled') && $exclude_paths && !preg_match($exclude_paths, $request->getPathInfo())) {
      $fast_paths = $config->get('fast_404.paths');
      if ($fast_paths && preg_match($fast_paths, $request->getPathInfo())) {
        $fast_404_html = strtr($config->get('fast_404.html'), ['@path' => Html::escape($request->getUri())]);
        $response = new HtmlResponse($fast_404_html, Response::HTTP_NOT_FOUND);
        // Some routes such as system.files conditionally throw a
        // NotFoundHttpException depending on URL parameters instead of just the
        // route and route parameters, so add the URL cache context to account
        // for this.
        $cacheable_metadata = new CacheableMetadata();
        $cacheable_metadata->setCacheContexts(['url']);
        $cacheable_metadata->addCacheTags(['4xx-response']);
        $response->addCacheableDependency($cacheable_metadata);
        $event->setResponse($response);
      }
    }
  }

  /**
   * Invalidates 4xx-response cache tag if fast 404 config is changed.
   *
   * @param \Drupal\Core\Config\ConfigCrudEvent $event
   *   The configuration event.
   */
  public function onConfigSave(ConfigCrudEvent $event): void {
    $saved_config = $event->getConfig();
    if ($saved_config->getName() === 'system.performance' && $event->isChanged('fast_404')) {
      $this->cacheTagsInvalidator->invalidateTags(['4xx-response']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = parent::getSubscribedEvents();
    $events[ConfigEvents::SAVE] = 'onConfigSave';
    return $events;
  }

}
