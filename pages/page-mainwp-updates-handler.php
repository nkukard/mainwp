<?php
/**
 * MainWP Updates Handler.
 */
namespace MainWP\Dashboard;

/**
 * MainWP Updates Page Handler.
 */
class MainWP_Updates_Handler {

	/**
	 * Get Class Name
	 *
	 * @return string __CLASS__
	 */
	public static function get_class_name() {
		return __CLASS__;
	}

	/**
	 * Check Child Site ID & Update.
	 *
	 * @throws MainWP_Exception on errors.
	 * @param mixed $id Child Site ID.
	 */
	public static function upgrade_site( $id ) {
		if ( isset( $id ) && MainWP_Utility::ctype_digit( $id ) ) {

			$website = MainWP_DB::instance()->get_website_by_id( $id );

			if ( MainWP_Utility::can_edit_website( $website ) ) {

				$information = MainWP_Connect::fetch_url_authed( $website, 'upgrade' );

				if ( isset( $information['upgrade'] ) && ( 'SUCCESS' === $information['upgrade'] ) ) {
					MainWP_DB::instance()->update_website_option( $website, 'wp_upgrades', wp_json_encode( array() ) );
					return '<i class="green check icon"></i>';
				} elseif ( isset( $information['upgrade'] ) ) {
					$errorMsg = '';
					if ( 'LOCALIZATION' === $information['upgrade'] ) {
						$errorMsg = '<i class="red times icon"></i> ' . __( 'No update found for the set locale.', 'mainwp' );
					} elseif ( 'NORESPONSE' === $information['upgrade'] ) {
						$errorMsg = '<i class="red times icon"></i> ' . __( 'No response from the child site server.', 'mainwp' );
					}
					throw new MainWP_Exception( 'WPERROR', $errorMsg );
				} elseif ( isset( $information['error'] ) ) {
					throw new MainWP_Exception( 'WPERROR', $information['error'] );
				} else {
					throw new MainWP_Exception( 'ERROR', '<i class="red times icon"></i> ' . __( 'Invalid response from child site.', 'mainwp' ) );
				}
			}
		}

		throw new MainWP_Exception( 'ERROR', '<i class="red times icon"></i> ' . __( 'Invalid request.', 'mainwp' ) );
	}

	/**
	 * Add a plugin or theme to the ignor list.
	 *
	 * @param mixed $type plugin|theme.
	 * @param mixed $slug Plugin or Theme Slug.
	 * @param mixed $name Plugin or Theme Name.
	 * @param mixed $id Child Site ID.
	 *
	 * @return string success.
	 */
	public static function ignore_plugin_theme( $type, $slug, $name, $id ) {
		if ( isset( $id ) && MainWP_Utility::ctype_digit( $id ) ) {
			$website = MainWP_DB::instance()->get_website_by_id( $id );
			if ( MainWP_Utility::can_edit_website( $website ) ) {
				$slug = urldecode( $slug );
				if ( 'plugin' === $type ) {
					$decodedIgnoredPlugins = json_decode( $website->ignored_plugins, true );
					if ( ! isset( $decodedIgnoredPlugins[ $slug ] ) ) {
						$decodedIgnoredPlugins[ $slug ] = urldecode( $name );
						MainWP_DB::instance()->update_website_values( $website->id, array( 'ignored_plugins' => wp_json_encode( $decodedIgnoredPlugins ) ) );
					}
				} elseif ( 'theme' === $type ) {
					$decodedIgnoredThemes = json_decode( $website->ignored_themes, true );
					if ( ! isset( $decodedIgnoredThemes[ $slug ] ) ) {
						$decodedIgnoredThemes[ $slug ] = urldecode( $name );
						MainWP_DB::instance()->update_website_values( $website->id, array( 'ignored_themes' => wp_json_encode( $decodedIgnoredThemes ) ) );
					}
				}
			}
		}

		return 'success';
	}

	/**
	 * Remove a plugin or theme from the ignore list.
	 *
	 * @param mixed $type plugin|theme.
	 * @param mixed $slug Plugin or Theme slug.
	 * @param mixed $id Plugin or Theme name.
	 *
	 * @return string success.
	 */
	public static function unignore_plugin_theme( $type, $slug, $id ) {
		if ( isset( $id ) ) {
			if ( '_ALL_' === $id ) {
				$websites = MainWP_DB::instance()->query( MainWP_DB::instance()->get_sql_websites_for_current_user() );
				while ( $websites && ( $website  = MainWP_DB::fetch_object( $websites ) ) ) {
					if ( 'plugin' === $type ) {
						MainWP_DB::instance()->update_website_values( $website->id, array( 'ignored_plugins' => wp_json_encode( array() ) ) );
					} elseif ( 'theme' === $type ) {
						MainWP_DB::instance()->update_website_values( $website->id, array( 'ignored_themes' => wp_json_encode( array() ) ) );
					}
				}
				MainWP_DB::free_result( $websites );
			} elseif ( MainWP_Utility::ctype_digit( $id ) ) {
				$website = MainWP_DB::instance()->get_website_by_id( $id );
				if ( MainWP_Utility::can_edit_website( $website ) ) {
					$slug = urldecode( $slug );
					if ( 'plugin' === $type ) {
						$decodedIgnoredPlugins = json_decode( $website->ignored_plugins, true );
						if ( isset( $decodedIgnoredPlugins[ $slug ] ) ) {
							unset( $decodedIgnoredPlugins[ $slug ] );
							MainWP_DB::instance()->update_website_values( $website->id, array( 'ignored_plugins' => wp_json_encode( $decodedIgnoredPlugins ) ) );
						}
					} elseif ( 'theme' === $type ) {
						$decodedIgnoredThemes = json_decode( $website->ignored_themes, true );
						if ( isset( $decodedIgnoredThemes[ $slug ] ) ) {
							unset( $decodedIgnoredThemes[ $slug ] );
							MainWP_DB::instance()->update_website_values( $website->id, array( 'ignored_themes' => wp_json_encode( $decodedIgnoredThemes ) ) );
						}
					}
				}
			}
		}

		return 'success';
	}

	/**
	 * Ignore Plugin or Themes.
	 *
	 * @param mixed $type plugin|theme
	 * @param mixed $slug Plugin or Theme slug.
	 * @param mixed $name Plugin or Theme name.
	 *
	 * @return string success.
	 */
	public static function ignore_plugins_themes( $type, $slug, $name ) {
		$slug          = urldecode( $slug );
		$userExtension = MainWP_DB_Common::instance()->get_user_extension();
		if ( 'plugin' === $type ) {
			$decodedIgnoredPlugins = json_decode( $userExtension->ignored_plugins, true );
			if ( ! is_array( $decodedIgnoredPlugins ) ) {
				$decodedIgnoredPlugins = array();
			}
			$decodedIgnoredPlugins[ $slug ] = urldecode( $name );
			MainWP_DB_Common::instance()->update_user_extension(
				array(
					'userid'             => null,
					'ignored_plugins'    => wp_json_encode( $decodedIgnoredPlugins ),
				)
			);
		} elseif ( 'theme' === $type ) {
			$decodedIgnoredThemes = json_decode( $userExtension->ignored_themes, true );
			if ( ! is_array( $decodedIgnoredThemes ) ) {
				$decodedIgnoredThemes = array();
			}
			$decodedIgnoredThemes[ $slug ] = urldecode( $name );
			MainWP_DB_Common::instance()->update_user_extension(
				array(
					'userid'         => null,
					'ignored_themes' => wp_json_encode( $decodedIgnoredThemes ),
				)
			);
		}

		return 'success';
	}

	/**
	 * Unignore Plugins or Themes.
	 *
	 * @param mixed $type plugin|themes.
	 * @param mixed $slug Plugin or Themes slug.
	 *
	 * @return string success.
	 */
	public static function unignore_plugins_themes( $type, $slug ) {
		$slug          = urldecode( $slug );
		$userExtension = MainWP_DB_Common::instance()->get_user_extension();
		if ( 'plugin' === $type ) {
			if ( '_ALL_' === $slug ) {
				$decodedIgnoredPlugins = array();
			} else {
				$decodedIgnoredPlugins = json_decode( $userExtension->ignored_plugins, true );
				if ( ! is_array( $decodedIgnoredPlugins ) ) {
					$decodedIgnoredPlugins = array();
				}
				if ( isset( $decodedIgnoredPlugins[ $slug ] ) ) {
					unset( $decodedIgnoredPlugins[ $slug ] );
				}
			}
			MainWP_DB_Common::instance()->update_user_extension(
				array(
					'userid'             => null,
					'ignored_plugins'    => wp_json_encode( $decodedIgnoredPlugins ),
				)
			);
		} elseif ( 'theme' === $type ) {
			if ( '_ALL_' === $slug ) {
				$decodedIgnoredThemes = array();
			} else {
				$decodedIgnoredThemes = json_decode( $userExtension->ignored_plugins, true );
				if ( ! is_array( $decodedIgnoredThemes ) ) {
					$decodedIgnoredThemes = array();
				}
				if ( isset( $decodedIgnoredThemes[ $slug ] ) ) {
					unset( $decodedIgnoredThemes[ $slug ] );
				}
			}
			MainWP_DB_Common::instance()->update_user_extension(
				array(
					'userid'         => null,
					'ignored_themes' => wp_json_encode( $decodedIgnoredThemes ),
				)
			);
		}

		return 'success';
	}


	/**
	 * Unignor abandoned plugins or themes.
	 *
	 * @param mixed $type plugin|themes.
	 * @param mixed $slug Plugin or Themes slug.
	 * @param mixed $id Child Site ID.
	 *
	 * @return string success.
	 */
	public static function unignore_abandoned_plugin_theme( $type, $slug, $id ) {
		if ( isset( $id ) ) {
			if ( '_ALL_' === $id ) {
				$websites = MainWP_DB::instance()->query( MainWP_DB::instance()->get_sql_websites_for_current_user() );
				while ( $websites && ( $website  = MainWP_DB::fetch_object( $websites ) ) ) {
					if ( 'plugin' === $type ) {
						MainWP_DB::instance()->update_website_option( $website, 'plugins_outdate_dismissed', wp_json_encode( array() ) );
					} elseif ( 'theme' === $type ) {
						MainWP_DB::instance()->update_website_option( $website, 'themes_outdate_dismissed', wp_json_encode( array() ) );
					}
				}
				MainWP_DB::free_result( $websites );
			} elseif ( MainWP_Utility::ctype_digit( $id ) ) {
				$website = MainWP_DB::instance()->get_website_by_id( $id );
				if ( MainWP_Utility::can_edit_website( $website ) ) {
					$slug = urldecode( $slug );
					if ( 'plugin' === $type ) {
						$decodedIgnoredPlugins = json_decode( MainWP_DB::instance()->get_website_option( $website, 'plugins_outdate_dismissed' ), true );
						if ( isset( $decodedIgnoredPlugins[ $slug ] ) ) {
							unset( $decodedIgnoredPlugins[ $slug ] );
							MainWP_DB::instance()->update_website_option( $website, 'plugins_outdate_dismissed', wp_json_encode( $decodedIgnoredPlugins ) );
						}
					} elseif ( 'theme' === $type ) {
						$decodedIgnoredThemes = json_decode( MainWP_DB::instance()->get_website_option( $website, 'themes_outdate_dismissed' ), true );
						if ( isset( $decodedIgnoredThemes[ $slug ] ) ) {
							unset( $decodedIgnoredThemes[ $slug ] );
							MainWP_DB::instance()->update_website_option( $website, 'themes_outdate_dismissed', wp_json_encode( $decodedIgnoredThemes ) );
						}
					}
				}
			}
		}

		return 'success';
	}


	/**
	 * Unignore abandoned plugins or themes.
	 *
	 * @param mixed $type plugin|theme.
	 * @param mixed $slug Plugin or Themes slug.
	 *
	 * @return string success.
	 */
	public static function unignore_abandoned_plugins_themes( $type, $slug ) {
		$slug          = urldecode( $slug );
		$userExtension = MainWP_DB_Common::instance()->get_user_extension();
		if ( 'plugin' === $type ) {
			if ( '_ALL_' === $slug ) {
				$decodedIgnoredPlugins = array();
			} else {
				$decodedIgnoredPlugins = json_decode( $userExtension->dismissed_plugins, true );
				if ( ! is_array( $decodedIgnoredPlugins ) ) {
					$decodedIgnoredPlugins = array();
				}
				if ( isset( $decodedIgnoredPlugins[ $slug ] ) ) {
					unset( $decodedIgnoredPlugins[ $slug ] );
				}
			}
			MainWP_DB_Common::instance()->update_user_extension(
				array(
					'userid'             => null,
					'dismissed_plugins'  => wp_json_encode( $decodedIgnoredPlugins ),
				)
			);
		} elseif ( 'theme' === $type ) {
			if ( '_ALL_' === $slug ) {
				$decodedIgnoredThemes = array();
			} else {
				$decodedIgnoredThemes = json_decode( $userExtension->dismissed_themes, true );
				if ( ! is_array( $decodedIgnoredThemes ) ) {
					$decodedIgnoredThemes = array();
				}
				if ( isset( $decodedIgnoredThemes[ $slug ] ) ) {
					unset( $decodedIgnoredThemes[ $slug ] );
				}
			}
			MainWP_DB_Common::instance()->update_user_extension(
				array(
					'userid'             => null,
					'dismissed_themes'   => wp_json_encode( $decodedIgnoredThemes ),
				)
			);
		}

		return 'success';
	}

	/**
	 * Dismis Plugin or Theme.
	 *
	 * @param mixed $type plugin|theme.
	 * @param mixed $slug Plugin or Theme slug.
	 * @param mixed $name Plugin or Theme name.
	 * @param mixed $id Child Site ID.
	 *
	 * @return string success.
	 */
	public static function dismiss_plugin_theme( $type, $slug, $name, $id ) {
		if ( isset( $id ) && MainWP_Utility::ctype_digit( $id ) ) {
			$website = MainWP_DB::instance()->get_website_by_id( $id );
			if ( MainWP_Utility::can_edit_website( $website ) ) {
				$slug = urldecode( $slug );
				if ( 'plugin' === $type ) {
					$decodedDismissedPlugins = json_decode( MainWP_DB::instance()->get_website_option( $website, 'plugins_outdate_dismissed' ), true );
					if ( ! isset( $decodedDismissedPlugins[ $slug ] ) ) {
						$decodedDismissedPlugins[ $slug ] = urldecode( $name );
						MainWP_DB::instance()->update_website_option( $website, 'plugins_outdate_dismissed', wp_json_encode( $decodedDismissedPlugins ) );
					}
				} elseif ( 'theme' === $type ) {
					$decodedDismissedThemes = json_decode( MainWP_DB::instance()->get_website_option( $website, 'themes_outdate_dismissed' ), true );
					if ( ! isset( $decodedDismissedThemes[ $slug ] ) ) {
						$decodedDismissedThemes[ $slug ] = urldecode( $name );
						MainWP_DB::instance()->update_website_option( $website, 'themes_outdate_dismissed', wp_json_encode( $decodedDismissedThemes ) );
					}
				}
			}
		}

		return 'success';
	}

	/**
	 * Dismiss plugins or themes.
	 *
	 * @param mixed $type plugin|theme.
	 * @param mixed $slug Plugin or Theme slug.
	 * @param mixed $name Plugin or Theme name.
	 *
	 * @return string success.
	 */
	public static function dismiss_plugins_themes( $type, $slug, $name ) {
		$slug          = urldecode( $slug );
		$userExtension = MainWP_DB_Common::instance()->get_user_extension();
		if ( 'plugin' === $type ) {
			$decodedDismissedPlugins = json_decode( $userExtension->dismissed_plugins, true );
			if ( ! is_array( $decodedDismissedPlugins ) ) {
				$decodedDismissedPlugins = array();
			}
			$decodedDismissedPlugins[ $slug ] = urldecode( $name );
			MainWP_DB_Common::instance()->update_user_extension(
				array(
					'userid'             => null,
					'dismissed_plugins'  => wp_json_encode( $decodedDismissedPlugins ),
				)
			);
		} elseif ( 'theme' === $type ) {
			$decodedDismissedThemes = json_decode( $userExtension->dismissed_themes, true );
			if ( ! is_array( $decodedDismissedThemes ) ) {
				$decodedDismissedThemes = array();
			}
			$decodedDismissedThemes[ $slug ] = urldecode( $name );
			MainWP_DB_Common::instance()->update_user_extension(
				array(
					'userid'             => null,
					'dismissed_themes'   => wp_json_encode( $decodedDismissedThemes ),
				)
			);
		}

		return 'success';
	}

	/**
	 * Upgrade plugin or theme translations.
	 *
	 * @throws MainWP_Exception on errors.
	 * @param mixed $id Child Site ID.
	 * @param mixed $type plugin|theme.
	 * @param mixed $list List of theme or plugin names ( seperated by , )
	 *
	 * @throw MainWP_Exception(error).
	 */
	public static function upgrade_plugin_theme_translation( $id, $type, $list ) {
		if ( isset( $id ) && MainWP_Utility::ctype_digit( $id ) ) {
			$website = MainWP_DB::instance()->get_website_by_id( $id );
			if ( MainWP_Utility::can_edit_website( $website ) ) {
				$information = MainWP_Connect::fetch_url_authed(
					$website,
					( 'translation' === $type ? 'upgradetranslation' : 'upgradeplugintheme' ),
					array(
						'type'   => $type,
						'list'   => urldecode( $list ),
					),
					true
				);
				if ( isset( $information['upgrades'] ) ) {
					$tmp = array();
					if ( isset( $information['upgrades'] ) ) {
						foreach ( $information['upgrades'] as $k => $v ) {
							$tmp[ rawurlencode( $k ) ] = $v;
						}
					}
					return $tmp;
				} elseif ( isset( $information['error'] ) ) {
					throw new MainWP_Exception( 'WPERROR', $information['error'] );
				} else {
					throw new MainWP_Exception( 'ERROR', 'Invalid response from site!' );
				}
			}
		}
		throw new MainWP_Exception( 'ERROR', __( 'Invalid request!', 'mainwp' ) );
	}

	/**
	 * Get plugin or theme slugs.
	 *
	 * @param mixed $id Child Site ID.
	 * @param mixed $type plugin|theme.
	 *
	 * @return array List of plugins or themes.
	 */
	public static function get_plugin_theme_slugs( $id, $type ) { // phpcs:ignore -- not quite complex function.

		$userExtension = MainWP_DB_Common::instance()->get_user_extension();
		$sql           = MainWP_DB::instance()->get_sql_website_by_id( $id );
		$websites      = MainWP_DB::instance()->query( $sql );
		$website       = MainWP_DB::fetch_object( $websites );

		$slugs = array();
		if ( 'plugin' === $type ) {
			if ( $website->is_ignorePluginUpdates ) {
				return '';
			}

			$plugin_upgrades        = json_decode( $website->plugin_upgrades, true );
			$decodedPremiumUpgrades = json_decode( MainWP_DB::instance()->get_website_option( $website, 'premium_upgrades' ), true );
			if ( is_array( $decodedPremiumUpgrades ) ) {
				foreach ( $decodedPremiumUpgrades as $crrSlug => $premiumUpgrade ) {
					$premiumUpgrade['premium'] = true;

					if ( 'plugin' === $premiumUpgrade['type'] ) {
						if ( ! is_array( $plugin_upgrades ) ) {
							$plugin_upgrades = array();
						}
						$premiumUpgrade              = array_filter( $premiumUpgrade );
						$plugin_upgrades[ $crrSlug ] = array_merge( $plugin_upgrades[ $crrSlug ], $premiumUpgrade );
					}
				}
			}

			$ignored_plugins = json_decode( $website->ignored_plugins, true );
			if ( is_array( $ignored_plugins ) ) {
				$plugin_upgrades = array_diff_key( $plugin_upgrades, $ignored_plugins );
			}

			$ignored_plugins = json_decode( $userExtension->ignored_plugins, true );
			if ( is_array( $ignored_plugins ) ) {
				$plugin_upgrades = array_diff_key( $plugin_upgrades, $ignored_plugins );
			}

			if ( is_array( $plugin_upgrades ) ) {
				foreach ( $plugin_upgrades as $slug => $plugin_upgrade ) {
					$slugs[] = rawurlencode( $slug );
				}
			}
		} elseif ( 'theme' === $type ) {

			if ( $website->is_ignoreThemeUpdates ) {
				return '';
			}

			$theme_upgrades         = json_decode( $website->theme_upgrades, true );
			$decodedPremiumUpgrades = json_decode( MainWP_DB::instance()->get_website_option( $website, 'premium_upgrades' ), true );
			if ( is_array( $decodedPremiumUpgrades ) ) {
				foreach ( $decodedPremiumUpgrades as $crrSlug => $premiumUpgrade ) {
					$premiumUpgrade['premium'] = true;

					if ( 'theme' === $premiumUpgrade['type'] ) {
						if ( ! is_array( $theme_upgrades ) ) {
							$theme_upgrades = array();
						}
						$theme_upgrades[ $crrSlug ] = $premiumUpgrade;
					}
				}
			}

			$ignored_themes = json_decode( $website->ignored_themes, true );
			if ( is_array( $ignored_themes ) ) {
				$theme_upgrades = array_diff_key( $theme_upgrades, $ignored_themes );
			}

			$ignored_themes = json_decode( $userExtension->ignored_themes, true );
			if ( is_array( $ignored_themes ) ) {
				$theme_upgrades = array_diff_key( $theme_upgrades, $ignored_themes );
			}

			if ( is_array( $theme_upgrades ) ) {
				foreach ( $theme_upgrades as $slug => $theme_upgrade ) {
					$slugs[] = $slug;
				}
			}
		} elseif ( 'translation' === $type ) {
			$translation_upgrades = json_decode( $website->translation_upgrades, true );
			if ( is_array( $translation_upgrades ) ) {
				foreach ( $translation_upgrades as $translation_upgrade ) {
					$slugs[] = $translation_upgrade['slug'];
				}
			}
		}

		return implode( ',', $slugs );
	}

}
