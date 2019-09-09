<?php

if ( ! defined( 'WPPF_PLUGIN_ACTIVE' ) ) {
	exit;
}

/**
 * Class WPPF_Admin_Profiler_List_Page
 */
class WPPF_Admin_Profiler_List_Page {

	/**
	 * WPPF_Admin_Profiler_List_Page constructor.
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
			__( 'Profiler List', 'wppf' ),
			__( 'Profiler List', 'wppf' ),
			'manage_options',
			WPPF::SLUG . '-profiler-list',
			array(
				$this,
				'renderPage'
			)
		);

		if ( ! $page_hook ) {
			add_action( 'admin_notices', function () {
				$class   = 'notice notice-error';
				$message = __( 'Could not create admin page to show profiler list.', 'wppf' );

				printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
			} );
		} else {
			add_action( 'load-' . $page_hook, array( $this, 'beforeRenderPage' ) );
		}

	}

	private $active_profiler_class;
	private $current_profiler_status;
	private $btn_values = [
		WPPF::PROFILER_ACTIVE   => "Enable",
		WPPF::PROFILER_INACTIVE => "Disable",
	];

	public function beforeRenderPage() {

		/** @var WPPF_Profiler_Base $class */
		$this->active_profiler_class = WPPF::getAllProfilerList()[0];

		if ( isset( $_GET['tab'] ) ) {
			/** @var WPPF_Profiler_Base $profiler */
			foreach ( WPPF::getAllProfilerList() as $profiler ) {

				if ( $profiler::getSlug() == $_GET['tab'] ) {
					$this->active_profiler_class = $profiler;
				}

			}
		}

		if ( isset( $_POST['status'] ) ) {

			WPPF::setProfilerStatus( $this->active_profiler_class, WPPF::isActiveProfiler( $this->active_profiler_class ) ? WPPF::PROFILER_INACTIVE : WPPF::PROFILER_ACTIVE );
		}

		call_user_func( array( $this->active_profiler_class, 'beforeRenderPage' ) );

	}

	/**
	 * Show Profiler settings
	 *
	 * Callback for admin page render
	 */
	public function renderPage() {

		?><h2 class="nav-tab-wrapper"><?php


		foreach ( WPPF::getAllProfilerList()  as $profiler ) {
			$active = '';
			if ($this->active_profiler_class == $profiler ) {
				$active = 'nav-tab-active';
			}
			?>
            <a class="nav-tab <?php echo $active; ?>"
               href="<?php echo admin_url( 'admin.php?page=' . WPPF::SLUG . '-profiler-list' . '&tab=' . $profiler::getSlug() ); ?>"><?php _e( $profiler::getName(), 'wppf' ); ?> </a>
			<?php
		}

		?></h2><?php

		$btn_status = WPPF::isActiveProfiler( $this->active_profiler_class ) ? WPPF::PROFILER_INACTIVE : WPPF::PROFILER_ACTIVE;
		?>

        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label > <?php esc_html_e( 'Enable / Disable Profiler', 'wppf' ); ?>
                        </label>
                    </th>
                    <td>
                        <form method="post">
                            <input type="hidden" name="status" value="<?php echo $btn_status; ?>">
                            <input type="submit" class=" <?php if( $btn_status == WPPF::PROFILER_ACTIVE ){ echo 'button-primary'; }else{ echo 'delete button-secondary'; } ?>" value="<?php echo $this->btn_values[ $btn_status ]; ?>">
                        </form>
                    </td>
                </tr>
                </tbody>
        </table>

		<?php
		call_user_func( array( $this->active_profiler_class, 'renderTab' ) );

	}

}