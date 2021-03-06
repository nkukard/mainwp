<?php
/**
 * MainWP Sync Handler
 *
 * Handle all syncing between MainWP & Child Site Network.
 *
 * @package     MainWP/Dashboard
 */

namespace MainWP\Dashboard;

/**
 * MainWP Sync Handler
 */
class MainWP_Sync {

	/**
	 * Method sync_site()
	 *
	 * @param mixed   $pWebsite Null|userid.
	 * @param boolean $pForceFetch Check if a fourced Sync.
	 * @param boolean $pAllowDisconnect Check if allowed to disconect.
	 *
	 * @return array sync_information_array
	 */
	public static function sync_site( &$pWebsite = null, $pForceFetch = false, $pAllowDisconnect = true ) {
		if ( null == $pWebsite ) {
			return false;
		}
		$userExtension = MainWP_DB_Common::instance()->get_user_extension_by_user_id( $pWebsite->userid );
		if ( null == $userExtension ) {
			return false;
		}

		MainWP_Utility::end_session();

		try {

			$cloneEnabled = apply_filters( 'mainwp_clone_enabled', false );
			$cloneSites   = array();
			if ( $cloneEnabled ) {
				$disallowedCloneSites = get_option( 'mainwp_clone_disallowedsites' );
				if ( false === $disallowedCloneSites ) {
					$disallowedCloneSites = array();
				}
				$websites = MainWP_DB::instance()->query( MainWP_DB::instance()->get_sql_websites_for_current_user() );
				if ( $websites ) {
					while ( $websites && ( $website = MainWP_DB::fetch_object( $websites ) ) ) {
						if ( in_array( $website->id, $disallowedCloneSites ) ) {
							continue;
						}
						if ( $website->id == $pWebsite->id ) {
							continue;
						}

						$cloneSites[ $website->id ] = array(
							'name'       => $website->name,
							'url'        => $website->url,
							'extauth'    => $website->extauth,
							'size'       => $website->totalsize,
						);
					}
					MainWP_DB::free_result( $websites );
				}
			}

			$primaryBackup = MainWP_System_Utility::get_primary_backup();

			$othersData = apply_filters_deprecated( 'mainwp-sync-others-data', array( array(), $pWebsite ), '4.0.7.2', 'mainwp_sync_others_data' );  // @deprecated Use 'mainwp_sync_others_data' instead.
			$othersData = apply_filters( 'mainwp_sync_others_data', $othersData, $pWebsite );

			$information = MainWP_Connect::fetch_url_authed(
				$pWebsite,
				'stats',
				array(
					'optimize'                       => ( ( get_option( 'mainwp_optimize' ) == 1 ) ? 1 : 0 ),
					'heatMap'                        => 0,
					'cloneSites'                     => ( ! $cloneEnabled ? 0 : rawurlencode( wp_json_encode( $cloneSites ) ) ),
					'othersData'                     => wp_json_encode( $othersData ),
					'server'                         => get_admin_url(),
					'numberdaysOutdatePluginTheme'   => get_option( 'mainwp_numberdays_Outdate_Plugin_Theme', 365 ),
					'primaryBackup'                  => $primaryBackup,
					'siteId'                         => $pWebsite->id,
				),
				true,
				$pForceFetch
			);
			MainWP_DB::instance()->update_website_option( $pWebsite, 'primary_lasttime_backup', isset( $information['primaryLasttimeBackup'] ) ? $information['primaryLasttimeBackup'] : 0 );
			$return = self::sync_information_array( $pWebsite, $information, '', 1, false, $pAllowDisconnect );

			return $return;
		} catch ( MainWP_Exception $e ) {
			$sync_errors          = '';
			$offline_check_result = 1;

			if ( $e->getMessage() == 'HTTPERROR' ) {
				$sync_errors          = __( 'HTTP error', 'mainwp' ) . ( $e->get_message_extra() != null ? ' - ' . $e->get_message_extra() : '' );
				$offline_check_result = - 1;
			} elseif ( $e->getMessage() == 'NOMAINWP' ) {
				$sync_errors          = __( 'MainWP Child plugin not detected', 'mainwp' );
				$offline_check_result = 1;
			}

			return self::sync_information_array( $pWebsite, $information, $sync_errors, $offline_check_result, true, $pAllowDisconnect );
		}
	}

	/**
	 * Method sync_information_array()
	 *
	 * Grab all Child Site Information.
	 *
	 * @param mixed   $pWebsite The website object.
	 * @param mixed   $information Filter mainwp_before_save_sync_result.
	 * @param string  $sync_errors Check for Sync Errors.
	 * @param integer $offline_check_result Check if offline.
	 * @param boolean $error True|False.
	 * @param boolean $pAllowDisconnect True|False.
	 *
	 * @return mixed do_action( 'mainwp-site-synced', $pWebsite, $information ).
	 */
	public static function sync_information_array( &$pWebsite, &$information, $sync_errors = '', $offline_check_result = 1, $error = false, $pAllowDisconnect = true ) { // phpcs:ignore -- complex method.
		$emptyArray        = wp_json_encode( array() );
		$websiteValues     = array(
			'directories'            => $emptyArray,
			'plugin_upgrades'        => $emptyArray,
			'theme_upgrades'         => $emptyArray,
			'translation_upgrades'   => $emptyArray,
			'securityIssues'         => $emptyArray,
			'themes'                 => $emptyArray,
			'plugins'                => $emptyArray,
			'users'                  => $emptyArray,
			'categories'             => $emptyArray,
			'offline_check_result'   => $offline_check_result,
		);
		$websiteSyncValues = array(
			'sync_errors'    => $sync_errors,
			'version'        => 0,
		);

		$done = false;

		$information = apply_filters( 'mainwp_before_save_sync_result', $information, $pWebsite );

		if ( isset( $information['siteurl'] ) ) {
			$websiteValues['siteurl'] = $information['siteurl'];
			$done                     = true;
		}

		if ( isset( $information['version'] ) ) {
			$websiteSyncValues['version'] = $information['version'];
			$done                         = true;
		}

		$phpversion = '';
		if ( isset( $information['site_info'] ) && null != $information['site_info'] ) {
			if ( is_array( $information['site_info'] ) && isset( $information['site_info']['phpversion'] ) ) {
				$phpversion = $information['site_info']['phpversion'];
			}
			MainWP_DB::instance()->update_website_option( $pWebsite, 'site_info', wp_json_encode( $information['site_info'] ) );
			$done = true;
		} else {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'site_info', $emptyArray );
		}
		MainWP_DB::instance()->update_website_option( $pWebsite, 'phpversion', $phpversion );

		if ( isset( $information['directories'] ) && is_array( $information['directories'] ) ) {
			$websiteValues['directories'] = wp_json_encode( $information['directories'] );
			$done                         = true;
		} elseif ( isset( $information['directories'] ) ) {
			$websiteValues['directories'] = $information['directories'];
			$done                         = true;
		}

		if ( isset( $information['wp_updates'] ) && null != $information['wp_updates'] ) {
			MainWP_DB::instance()->update_website_option(
				$pWebsite,
				'wp_upgrades',
				wp_json_encode(
					array(
						'current'    => $information['wpversion'],
						'new'        => $information['wp_updates'],
					)
				)
			);
			$done = true;
		} else {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'wp_upgrades', $emptyArray );
		}

		if ( isset( $information['plugin_updates'] ) ) {
			$websiteValues['plugin_upgrades'] = wp_json_encode( $information['plugin_updates'] );
			$done                             = true;
		}

		if ( isset( $information['theme_updates'] ) ) {
			$websiteValues['theme_upgrades'] = wp_json_encode( $information['theme_updates'] );
			$done                            = true;
		}

		if ( isset( $information['translation_updates'] ) ) {
			$websiteValues['translation_upgrades'] = wp_json_encode( $information['translation_updates'] );
			$done                                  = true;
		}

		if ( isset( $information['premium_updates'] ) ) {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'premium_upgrades', wp_json_encode( $information['premium_updates'] ) );
			$done = true;
		} else {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'premium_upgrades', $emptyArray );
		}

		if ( isset( $information['securityIssues'] ) && MainWP_Utility::ctype_digit( $information['securityIssues'] ) && $information['securityIssues'] >= 0 ) {
			$websiteValues['securityIssues'] = $information['securityIssues'];
			$done                            = true;
		}

		if ( isset( $information['recent_comments'] ) ) {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'recent_comments', wp_json_encode( $information['recent_comments'] ) );
			$done = true;
		} else {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'recent_comments', $emptyArray );
		}

		if ( isset( $information['recent_posts'] ) ) {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'recent_posts', wp_json_encode( $information['recent_posts'] ) );
			$done = true;
		} else {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'recent_posts', $emptyArray );
		}

		if ( isset( $information['recent_pages'] ) ) {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'recent_pages', wp_json_encode( $information['recent_pages'] ) );
			$done = true;
		} else {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'recent_pages', $emptyArray );
		}

		if ( isset( $information['themes'] ) ) {
			$websiteValues['themes'] = wp_json_encode( $information['themes'] );
			$done                    = true;
		}

		if ( isset( $information['plugins'] ) ) {
			$websiteValues['plugins'] = wp_json_encode( $information['plugins'] );
			$done                     = true;
		}

		if ( isset( $information['users'] ) ) {
			$websiteValues['users'] = wp_json_encode( $information['users'] );
			$done                   = true;
		}

		if ( isset( $information['categories'] ) ) {
			$websiteValues['categories'] = wp_json_encode( $information['categories'] );
			$done                        = true;
		}

		if ( isset( $information['totalsize'] ) ) {
			$websiteSyncValues['totalsize'] = $information['totalsize'];
			$done                           = true;
		}

		if ( isset( $information['dbsize'] ) ) {
			$websiteSyncValues['dbsize'] = $information['dbsize'];
			$done                        = true;
		}

		if ( isset( $information['extauth'] ) ) {
			$websiteSyncValues['extauth'] = $information['extauth'];
			$done                         = true;
		}

		if ( isset( $information['nossl'] ) ) {
			$websiteValues['nossl'] = $information['nossl'];
			$done                   = true;
		}

		if ( isset( $information['wpe'] ) ) {
			$websiteValues['wpe'] = $information['wpe'];
			$done                 = true;
		}

		if ( isset( $information['last_post_gmt'] ) ) {
			$websiteSyncValues['last_post_gmt'] = $information['last_post_gmt'];
			$done                               = true;
		}

		if ( isset( $information['health_site_status'] ) ) {
			$issue_counts                       = $information['health_site_status'];
			$websiteSyncValues['health_issues'] = $issue_counts['critical'];
			$done                               = true;
			MainWP_DB::instance()->update_website_option( $pWebsite, 'health_site_status', wp_json_encode( $issue_counts ) ); // used in reports.
		} else {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'health_site_status', $emptyArray );
		}

		if ( isset( $information['mainwpdir'] ) ) {
			$websiteValues['mainwpdir'] = $information['mainwpdir'];
			$done                       = true;
		}

		if ( isset( $information['uniqueId'] ) ) {
			$websiteValues['uniqueId'] = $information['uniqueId'];
			$done                      = true;
		}

		if ( isset( $information['admin_nicename'] ) ) {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'admin_nicename', trim( $information['admin_nicename'] ) );
			$done = true;
		}

		if ( isset( $information['admin_useremail'] ) ) {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'admin_useremail', trim( $information['admin_useremail'] ) );
			$done = true;
		}

		if ( isset( $information['plugins_outdate_info'] ) ) {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'plugins_outdate_info', wp_json_encode( $information['plugins_outdate_info'] ) );
			$done = true;
		} else {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'plugins_outdate_info', $emptyArray );
		}

		if ( isset( $information['themes_outdate_info'] ) ) {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'themes_outdate_info', wp_json_encode( $information['themes_outdate_info'] ) );
			$done = true;
		} else {
			MainWP_DB::instance()->update_website_option( $pWebsite, 'themes_outdate_info', $emptyArray );
		}

		if ( ! $done ) {
			if ( isset( $information['wpversion'] ) ) {
				$done = true;
			} elseif ( isset( $information['error'] ) ) {
				MainWP_Logger::instance()->warning_for_website( $pWebsite, 'SYNC ERROR', '[' . $information['error'] . ']' );
				$error                            = true;
				$done                             = true;
				$websiteSyncValues['sync_errors'] = __( 'ERROR: ', 'mainwp' ) . $information['error'];
			} elseif ( ! empty( $sync_errors ) ) {
				MainWP_Logger::instance()->warning_for_website( $pWebsite, 'SYNC ERROR', '[' . $sync_errors . ']' );

				$error = true;
				if ( ! $pAllowDisconnect ) {
					$sync_errors = '';
				}

				$websiteSyncValues['sync_errors'] = $sync_errors;
			} else {
				MainWP_Logger::instance()->warning_for_website( $pWebsite, 'SYNC ERROR', '[Undefined error]' );
				$error = true;
				if ( $pAllowDisconnect ) {
					$websiteSyncValues['sync_errors'] = __( 'Undefined error! Please, reinstall the MainWP Child plugin on the child site.', 'mainwp' );
				}
			}
		}

		if ( $done ) {
			$websiteSyncValues['dtsSync'] = time();
		}
		MainWP_DB::instance()->update_website_sync_values( $pWebsite->id, $websiteSyncValues );
		MainWP_DB::instance()->update_website_values( $pWebsite->id, $websiteValues );

		// Sync action.
		if ( ! $error ) {
			do_action_deprecated( 'mainwp-site-synced', array( $pWebsite, $information ), '4.0.7.2', 'mainwp_site_synced' ); // @deprecated Use 'mainwp_site_synced' instead.
			do_action( 'mainwp_site_synced', $pWebsite, $information );
		}

		return ( ! $error );
	}

	/**
	 * Method sync_site_icon()
	 *
	 * Get site's icon.
	 *
	 * @param mixed $siteId site's id.
	 * @return array result error or success
	 */
	public static function sync_site_icon( $siteId = null ) {
		if ( MainWP_Utility::ctype_digit( $siteId ) ) {
			$website = MainWP_DB::instance()->get_website_by_id( $siteId );
			if ( MainWP_System_Utility::can_edit_website( $website ) ) {
				$error = '';
				try {
					$information = MainWP_Connect::fetch_url_authed( $website, 'get_site_icon' );
				} catch ( MainWP_Exception $e ) {
					$error = $e->getMessage();
				}

				if ( '' != $error ) {
					return array( 'error' => $error );
				} elseif ( isset( $information['faviIconUrl'] ) && ! empty( $information['faviIconUrl'] ) ) {
					MainWP_Logger::instance()->debug( 'Downloading icon :: ' . $information['faviIconUrl'] );
					$content = MainWP_Connect::get_file_content( $information['faviIconUrl'] );
					if ( ! empty( $content ) ) {

						$hasWPFileSystem = MainWP_System_Utility::get_wp_file_system();
						global $wp_filesystem;

						$dirs     = MainWP_System_Utility::get_mainwp_dir();
						$iconsDir = $dirs[0] . 'icons' . DIRECTORY_SEPARATOR;
						if ( $hasWPFileSystem && ! $wp_filesystem->is_dir( $iconsDir ) ) {
							$wp_filesystem->mkdir( $iconsDir, 0777, true );
						}
						if ( $hasWPFileSystem && ! $wp_filesystem->exists( $iconsDir . 'index.php' ) ) {
							$wp_filesystem->touch( $iconsDir . 'index.php' );
						}
						$filename = basename( $information['faviIconUrl'] );
						$filename = strtok( $filename, '?' );
						if ( $filename ) {
							$filename = 'favi-' . $siteId . '-' . $filename;
							$size     = $wp_filesystem->put_contents( $iconsDir . $filename, $content ); // phpcs:ignore --
							if ( $size ) {
								MainWP_Logger::instance()->debug( 'Icon size :: ' . $size );
								MainWP_DB::instance()->update_website_option( $website, 'favi_icon', $filename );
								return array( 'result' => 'success' );
							} else {
								return array( 'error' => 'Save icon file failed.' );
							}
						}
						return array( 'undefined_error' => true );
					} else {
						return array( 'error' => esc_html__( 'Download icon file failed', 'mainwp' ) );
					}
				} else {
					return array( 'undefined_error' => true );
				}
			}
		}
		return array( 'result' => 'NOSITE' );
	}

}
