<?php
/**
 * Automatic updates from the cotlas-travel GitHub releases endpoint.
 *
 * Public repo, so no token is required. Define COTLAS_GITHUB_TOKEN in
 * wp-config.php if the repo is ever made private or to raise API rate limits.
 *
 * @package CotlasTravel
 */

defined( 'ABSPATH' ) || exit;

if ( is_admin() ) {
	new Cotlas_Travel_GitHub_Updater( defined( 'COTLAS_TRAVEL_FILE' ) ? COTLAS_TRAVEL_FILE : __FILE__ );
}

/**
 * Cotlas_Travel_GitHub_Updater.
 */
class Cotlas_Travel_GitHub_Updater {

	/**
	 * Main plugin file.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * Plugin basename, e.g. cotlas-travel/cotlas-travel.php.
	 *
	 * @var string
	 */
	private $plugin_slug;

	/**
	 * Cached plugin headers.
	 *
	 * @var array
	 */
	private $plugin_data = array();

	/**
	 * GitHub username/repo-name.
	 *
	 * @var string
	 */
	private $github_repo = 'cotlaswebhost/cotlas-travel';

	/**
	 * Constructor.
	 *
	 * @param string $file Main plugin file.
	 */
	public function __construct( $file ) {
		$this->file        = $file;
		$this->plugin_slug = plugin_basename( $file );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
		add_filter( 'http_request_args', array( $this, 'add_auth_header' ), 10, 2 );
	}

	/**
	 * Read the plugin headers once.
	 */
	private function load_plugin_data() {
		if ( empty( $this->plugin_data ) ) {
			$this->plugin_data = get_plugin_data( $this->file );
		}
	}

	/**
	 * Inject the GitHub auth token for github.com requests (private repos).
	 *
	 * @param array  $args Request args.
	 * @param string $url  Request URL.
	 * @return array
	 */
	public function add_auth_header( $args, $url ) {
		$token = defined( 'COTLAS_GITHUB_TOKEN' ) ? COTLAS_GITHUB_TOKEN : '';
		if ( $token && false !== strpos( $url, 'github.com' ) ) {
			if ( ! isset( $args['headers'] ) ) {
				$args['headers'] = array();
			}
			$args['headers']['Authorization'] = 'Bearer ' . $token;
		}
		return $args;
	}

	/**
	 * Fetch the latest release from the GitHub API.
	 *
	 * @return array|false
	 */
	private function get_release_info() {
		static $release = null;

		if ( null !== $release ) {
			return $release;
		}

		$args = array(
			'headers' => array(
				'Accept'               => 'application/vnd.github+json',
				'X-GitHub-Api-Version' => '2022-11-28',
			),
			'timeout' => 10,
		);

		$token = defined( 'COTLAS_GITHUB_TOKEN' ) ? COTLAS_GITHUB_TOKEN : '';
		if ( $token ) {
			$args['headers']['Authorization'] = 'Bearer ' . $token;
		}

		$url      = 'https://api.github.com/repos/' . $this->github_repo . '/releases/latest';
		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
			$release = false;
			return false;
		}

		$release = json_decode( wp_remote_retrieve_body( $response ), true );
		return $release;
	}

	/**
	 * Tell WordPress when a newer release is available.
	 *
	 * @param object $transient Update transient.
	 * @return object
	 */
	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$this->load_plugin_data();
		$release = $this->get_release_info();

		if ( ! $release ) {
			return $transient;
		}

		$remote_version = ltrim( $release['tag_name'], 'v' );

		if ( version_compare( $this->plugin_data['Version'], $remote_version, '<' ) ) {
			// Prefer a release asset ZIP; fall back to the source zipball.
			$zip_url = $release['zipball_url'];
			if ( ! empty( $release['assets'] ) ) {
				foreach ( $release['assets'] as $asset ) {
					if ( isset( $asset['content_type'] ) && 'application/zip' === $asset['content_type'] ) {
						$zip_url = $asset['browser_download_url'];
						break;
					}
				}
			}

			$transient->response[ $this->plugin_slug ] = (object) array(
				'slug'        => dirname( $this->plugin_slug ),
				'plugin'      => $this->plugin_slug,
				'new_version' => $remote_version,
				'url'         => 'https://github.com/' . $this->github_repo,
				'package'     => $zip_url,
				'icons'       => array(),
			);
		}

		return $transient;
	}

	/**
	 * Populate the "View version details" popup.
	 *
	 * @param mixed  $result Result object.
	 * @param string $action API action.
	 * @param object $args   API args.
	 * @return mixed
	 */
	public function plugin_popup( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}
		if ( ! isset( $args->slug ) || dirname( $this->plugin_slug ) !== $args->slug ) {
			return $result;
		}

		$this->load_plugin_data();
		$release = $this->get_release_info();

		if ( ! $release ) {
			return $result;
		}

		return (object) array(
			'name'          => $this->plugin_data['Name'],
			'slug'          => dirname( $this->plugin_slug ),
			'version'       => ltrim( $release['tag_name'], 'v' ),
			'author'        => $this->plugin_data['Author'],
			'homepage'      => $this->plugin_data['PluginURI'],
			'sections'      => array(
				'description' => $this->plugin_data['Description'],
				'changelog'   => nl2br( isset( $release['body'] ) ? esc_html( $release['body'] ) : '' ),
			),
			'download_link' => isset( $release['zipball_url'] ) ? $release['zipball_url'] : '',
		);
	}

	/**
	 * After install, rename GitHub's auto-generated folder to cotlas-travel/.
	 * Source ZIPs extract to something like USERNAME-cotlas-travel-abc123/.
	 *
	 * @param array  $response  Install response.
	 * @param array  $hook_extra Extra hook args.
	 * @param array  $result    Install result.
	 * @return array
	 */
	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem;

		if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->plugin_slug ) {
			return $result;
		}

		$dest = WP_PLUGIN_DIR . '/' . dirname( $this->plugin_slug );

		if ( $wp_filesystem->exists( $dest ) ) {
			$wp_filesystem->delete( $dest, true );
		}

		$wp_filesystem->move( $result['destination'], $dest );
		$result['destination'] = $dest;

		return $result;
	}
}
