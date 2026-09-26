<?php

namespace Pusher\WordPress;

include_once(ABSPATH . 'wp-admin/includes/plugin.php');
include_once(ABSPATH . 'wp-admin/includes/file.php');
include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
include_once(ABSPATH . 'wp-admin/includes/misc.php');

use Plugin_Upgrader;
use Pusher\Log\Logger;
use Pusher\Plugin;
use stdClass;

class PluginUpgrader extends Plugin_Upgrader
{
    public $plugin;

    /**
     * @param PluginUpgraderSkin $skin
     */
    public function __construct(PluginUpgraderSkin $skin)
    {
        parent::__construct($skin);
    }

    public function installPlugin(Plugin $plugin)
    {
        add_filter('upgrader_source_selection', array($this, 'upgraderSourceSelectionFilter'), 10, 3);

        $this->plugin = $plugin;

        $zipUrl = apply_filters('wppusher_get_zip_url', $this->plugin->repository->getZipUrl(), $plugin);

        parent::install($zipUrl);

        // Make sure we get out of maintenance mode
        $this->maintenance_mode(false);
    }

    public function upgradePlugin(Plugin $plugin)
    {
        $reActivatePlugin = is_plugin_active((string) $plugin);
        $reActivatePluginNetworkWide = is_plugin_active_for_network((string) $plugin);

        add_filter("pre_site_transient_update_plugins", array($this, 'preSiteTransientUpdatePluginsFilter'), 10, 3);
        add_filter('upgrader_source_selection', array($this, 'upgraderSourceSelectionFilter'), 10, 3);

        $this->plugin = $plugin;

        try {
            parent::upgrade($this->plugin->file);
        } finally {
            // Make sure we get out of maintenance mode, also when the update failed.
            // The skin throws out of parent::upgrade(), so without the finally neither
            // this nor WordPress' own call runs and the site stays in maintenance mode
            // until someone deletes .maintenance by hand.
            $this->maintenance_mode(false);
        }

        if ($reActivatePlugin) {
            if ( ! is_plugin_active((string) $plugin))
                activate_plugin($plugin, null, $network_wide = $reActivatePluginNetworkWide, $silent = true);
        }
    }

    public function upgraderSourceSelectionFilter($source, $remote_source, $upgrader)
    {
        $repositoryRoot = $source;

        if ($upgrader->plugin->hasSubdirectory()) {
            $source = trailingslashit($source) . trailingslashit($upgrader->plugin->getSubdirectory());
        }

        $newSource = trailingslashit($remote_source) . trailingslashit($upgrader->plugin->getSlug());

        global $wp_filesystem;

        if ( ! is_object($wp_filesystem)) {
            return new \WP_Error('wppusher_no_filesystem', "WP Pusher could not access the WordPress filesystem. Check the <code>FS_METHOD</code> constant and the filesystem credentials for this site.");
        }

        if ( ! $wp_filesystem->is_dir($source)) {
            return $this->sourceNotFoundError($upgrader->plugin, $source, $repositoryRoot);
        }

        if ( ! $wp_filesystem->move($source, $newSource, true)) {
            return $this->moveFailedError($source, $newSource);
        }

        return $newSource;
    }

    /**
     * The path we were about to move doesn't exist. With a subdirectory configured
     * that means the subdirectory isn't in the repository; without one it means the
     * checkout itself is empty.
     */
    protected function sourceNotFoundError(Plugin $plugin, $source, $repositoryRoot)
    {
        if ($plugin->hasSubdirectory()) {
            return new \WP_Error('wppusher_subdirectory', sprintf(
                "WP Pusher couldn't find the subdirectory <code>%s</code> in the repository.%s",
                esc_html($plugin->getSubdirectory()),
                $this->renderRepositoryListing($repositoryRoot)
            ));
        }

        return new \WP_Error('wppusher_source_not_found', sprintf(
            "WP Pusher couldn't find the downloaded files on disk (<code>%s</code>). The package was downloaded, but the unpacked checkout is empty or not where WordPress expected it.",
            esc_html($source)
        ));
    }

    /**
     * List the top level of the repository, so a mistyped subdirectory is visible
     * from the error message instead of requiring a manual checkout.
     */
    protected function renderRepositoryListing($repositoryRoot)
    {
        global $wp_filesystem;

        $list = $wp_filesystem->dirlist($repositoryRoot);

        if ( ! is_array($list) or empty($list)) {
            return '';
        }

        $names = array_slice(array_keys($list), 0, 20);

        return ' The repository root contains: <code>' . esc_html(implode(', ', $names)) . '</code>.';
    }

    /**
     * The source exists, so the repository layout is fine and the move itself failed -
     * usually permissions, ownership or a filesystem method that can't rename. This
     * used to be reported as a missing subdirectory, which sent people looking in the
     * wrong place.
     */
    protected function moveFailedError($source, $newSource)
    {
        global $wp_filesystem;

        $method = 'unknown';

        if (isset($wp_filesystem->method) and $wp_filesystem->method) {
            $method = $wp_filesystem->method;
        }

        $message = sprintf(
            "WP Pusher downloaded and unpacked the package, but could not move it into place. This is not a repository layout problem - the files are there. Tried to move <code>%s</code> to <code>%s</code> using the <code>%s</code> filesystem method. Check permissions and ownership on the WordPress uploads and plugins directories.",
            esc_html($source),
            esc_html($newSource),
            esc_html($method)
        );

        if (isset($wp_filesystem->errors) and is_wp_error($wp_filesystem->errors) and $wp_filesystem->errors->get_error_code()) {
            $message .= ' Filesystem error: <code>' . esc_html($wp_filesystem->errors->get_error_message()) . '</code>';
        }

        return new \WP_Error('wppusher_move_failed', $message);
    }

    public function preSiteTransientUpdatePluginsFilter($transient)
    {
        $zipUrl = apply_filters('wppusher_get_zip_url', $this->plugin->repository->getZipUrl(), $this->plugin);

        $options = array('package' => $zipUrl);

        // If $transient doesn't exist - create it
        if (! $transient) {
            $transient = new stdClass;
        };

        $transient->response[$this->plugin->file] = (object) $options;

        return $transient;
    }
}
