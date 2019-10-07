<?php

namespace WPPF\views;

use WPPF\profilers\Profiler;
use WPPF\WPPF;

/**
 * Class WPPF_Admin_Settings_Page
 */
class AdminSettingsPage {

	/**
	 * WPPF_Admin_Settings_Page constructor.
	 *
	 */
	public function __construct() {
		$this->add_submenu_page();
	}

	// endregion

	// region Page

	/**
	 */
	protected function add_submenu_page() {
		$page_hook = add_submenu_page(
			WPPF::SLUG,
			__( 'Settings', 'wppf' ),
			__( 'Settings', 'wppf' ),
			'manage_options',
			WPPF::SLUG,
			array(
				$this,
				'on_hook_page_settings'
			)
		);

		if ( ! $page_hook ) {
			add_action( 'admin_notices', function () {
				$class   = 'notice notice-error';
				$message = __( 'Could not create admin page to show settings.', 'wppf' );

				printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
			} );
		} else {
			add_action( 'load-' . $page_hook, array( $this, 'on_hook_page_load_process_settings' ) );
		}

	}

	public function on_hook_page_load_process_settings() {
		if ( isset( $_POST['wppf-profiler'] ) && is_array( $_POST['wppf-profiler'] ) ) {

			if ( WPPF::setOption( 'active_profiler_list', $_POST['wppf-profiler'] ) ) {

				add_action( 'admin_notices', function () {
					$class   = 'notice notice-success';
					$message = __( 'Saved!', 'wppf' );

					printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
				} );
				if ( wp_redirect( admin_url( 'admin.php?page=' . WPPF::SLUG ) ) ) {
					exit;
				}
			} else {
				add_action( 'admin_notices', function () {
					$class   = 'notice notice-error';
					$message = __( 'No changes!', 'wppf' );

					printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
				} );
			}
		}
	}

	/**
	 * Show Profiler settings
	 *
	 * Callback for admin page render
	 */
	public function on_hook_page_settings() {
		?>
        <div class="wrap">
        <h1><?php esc_html_e( 'WP Profiler Settings', 'wppf' ); ?></h1>
        <form method="post">
            <table class="form-table">
                <tbody>
				<?php
				foreach ( WPPF::getAllProfilerList() as $profiler ):
					/** @var Profiler $class */
					$class = "WPPF\\profilers\\" . $profiler;
					?>
                    <tr>
                        <th scope="row">
                            <label for="<?php echo $profiler; ?>">
								<?php echo $class::getName(); ?>
                            </label>
                        </th>
                        <td>
                            <fieldset>
                                <label class="description" for="<?php echo $class::getSlug(); ?>">
                                    <input name="wppf-profiler[]" id="<?php echo $class::getSlug(); ?>"
                                           value="<?php echo $profiler; ?>" type="checkbox"
                                           class="regular-text" <?php echo WPPF::isActiveProfiler( $profiler ) ? 'checked' : ''; ?> >
									<?php esc_html_e( 'Enable', 'wppf' );
									echo ' "' . $class::getName() . '"'; ?>
                                </label>
                                <p class="description">
									<?php
									esc_html_e( 'Enable / Disable', 'wppf' );
									echo ' "' . $class::getName() . '"';
									?>
                                </p>
                            </fieldset>
                        </td>
                    </tr>

				<?php endforeach; ?>
                </tbody>
            </table>
			<?php submit_button( 'Save Changes', 'primary', 'submit', true, array() ); ?>
        </div><?php
	}


}