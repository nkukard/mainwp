<?php
class MainWPExtensionsView
{
    public static function initMenu()
    {        
        add_submenu_page('mainwp_tab', __('Extensions', 'mainwp'), ' <span id="mainwp-Extensions">' . __('Extensions', 'mainwp') . '</span>', 'read', 'Extensions', array(MainWPExtensions::getClassName(), 'render'));     
    }

    public static function renderHeader($shownPage, &$extensions)
    {        
        ?>
    <div class="wrap">
        <a href="https://mainwp.com" id="mainwplogo" title="MainWP" target="_blank"><img
                src="<?php echo plugins_url('images/logo.png', dirname(__FILE__)); ?>" height="50"
                alt="MainWP"/></a>
        <h2><i class="fa fa-plug"></i> <?php _e('Extensions', 'mainwp'); ?></h2><div style="clear: both;"></div><br/><br/>
        <?php if ($shownPage === '') { ?>
        <div id="mainwp-extensions-categories-menu" class="postbox">
                <div class="mainwp-inside"><span id="mainwp-extensions-menu-title"><?php _e('Get MainWP Extensions','mainwp');?></span></div>
                <div style="border-bottom: 1px Solid #e5e5e5;"></div>
                <div class="mainwp-inside mainwp-align-center" style="clear: both;">
                <div id="mainwp-extensions-cat-menu">
                    <ul id="mainwp-extensions-menu-cat-list">
                        <li class="mainwp-extensions-menu-item mainwp-category-1"><a href="https://extensions.mainwp.com/product-category/mainwp-extensions/administrative/"><?php _e('Administrative','mainwp'); ?></a></li>
                        <li class="mainwp-extensions-menu-item mainwp-category-2"><a href="https://extensions.mainwp.com/product-category/mainwp-extensions/content/"><?php _e('Content','mainwp'); ?></a></li>
                        <li class="mainwp-extensions-menu-item mainwp-category-3"><a href="https://extensions.mainwp.com/product-category/mainwp-extensions/visitor-data/"><?php _e('Visitor Data','mainwp'); ?></a></li>
                        <li class="mainwp-extensions-menu-item mainwp-category-4"><a href="https://extensions.mainwp.com/product-category/mainwp-extensions/free/"><?php _e('Free Extensions','mainwp'); ?></a></li>
                        <li class="mainwp-extensions-menu-item mainwp-category-5"><a href="https://extensions.mainwp.com/shop/"><?php _e('All Extensions','mainwp'); ?></a></li>
                    </ul>
                </div>
                    <div style="clear: both;"></div>
                </div>
        </div>        
       
<?php     
    
    $loader_url = '<i class="fa fa-spinner fa-pulse"></i>';
    if (mainwp_current_user_can("dashboard", "bulk_install_and_activate_extensions")) {     

        $username = $password = "";
        $checked_save = false;
        if (get_option("mainwp_extensions_api_save_login") == true) {            
            $enscrypt_u = get_option('mainwp_extensions_api_username');
            $enscrypt_p = get_option('mainwp_extensions_api_password');
            $username = !empty($enscrypt_u) ? MainWPApiManagerPasswordManagement::decrypt_string($enscrypt_u) : "";
            $password = !empty($enscrypt_p) ? MainWPApiManagerPasswordManagement::decrypt_string($enscrypt_p) : "";             
            $checked_save = true;
        } 
    
        if (!MainWPUtility::resetUserCookie('api_bulk_install')) {
        ?>
            <span id="mainwp_api_postbox_reset_showhide"></span>
        <?php 
        }        
?>
        
    <div class="postbox mainwp_api_postbox" section="1" >
           <!-- <div class="handlediv"><br></div> -->
           <h3 class="mainwp_box_title"><span><i class="fa fa-cog"></i> <?php _e("Bulk Install and Activate Extensions", "mainwp"); ?></span></h3>                          
           <div class="mainwp-inside" style="clear: both;">
            <div style="padding: 0 5px;">
            <?php 
                
                if (get_option('mainwp_api_sslVerifyCertificate') == 1) {
                    update_option('mainwp_api_sslVerifyCertificate', 0);                     
                } 
                
//                $apisslverify = get_option('mainwp_api_sslVerifyCertificate');  
//                if (defined('OPENSSL_VERSION_NUMBER') && (OPENSSL_VERSION_NUMBER <= 0x009080bf) && ($apisslverify === false)) {                                       
//                    $apisslverify = 0;
//                    MainWPUtility::update_option("mainwp_api_sslVerifyCertificate", $apisslverify);
//                }
//                $_selected_1 = (($apisslverify === false) || ($apisslverify == 1)) ? "selected" : ''; 
//                $_selected_0 = empty($_selected_1) ? "selected" : "";                
                 
                ?>      
             
                <strong><?php _e("Step 1", "mainwp"); ?></strong>
                <p><span class="description"><?php _e("Enter your MainWP Extensions (https://extensions.mainwp.com) Login to automatically install and activate purchased extensions."); ?></span></p>
                <span><?php _e("MainWP Extensions Login:", "mainwp"); ?></span><br /><br />
                <div class="api-grabbing-fields">              
                    <input type="text" class="input username" placeholder="<?php echo __("Username", "mainwp"); ?>" value="<?php echo $username; ?>"/>&nbsp;
                    <input type="password" class="input passwd" placeholder="<?php echo __("Password", "mainwp"); ?>" value="<?php echo $password; ?>"/>&nbsp;
                    <label><input type="checkbox" <?php echo $checked_save ? 'checked="checked"' : ""; ?> name="extensions_api_savemylogin_chk" id="extensions_api_savemylogin_chk"><?php _e("Save API login", "mainwp"); ?></label>
                </div>  
                <p>
                    <span class="extension_api_loading">
                        <input type="button" class="button-primary" id="mainwp-extensions-savelogin" value="<?php _e("Save Login", "mainwp"); ?>">
                        <i class="fa fa-spinner fa-pulse" style="display: none;"></i><span class="status hidden"></span>
                    </span>
                </p>  
                <p><hr></p>            
                <strong><?php _e("Step 2", "mainwp"); ?></strong>
                <div id="mainwp-install-purchased-extensions">
                <p><span class="description"><?php _e("The Install Purchased Extensions button will automatically install all your MainWP Extensions. You can also install them manually using the directions <a href=\"http://docs.mainwp.com/how-to-install-mainwp-extensions/\" >here</a>."); ?></span></p>
                <p>
                    <span class="extension_api_loading">
                        <input type="button" class="mainwp-upgrade-button button-primary" id="mainwp-extensions-bulkinstall" value="<?php _e("Install purchased extensions", "mainwp"); ?>">
                        <i class="fa fa-spinner fa-pulse" style="display: none;"></i><span class="status hidden"></span>
                    </span>
                </p>                            
                </div>
                <p><hr></p>
                <strong><?php _e("Step 3", "mainwp"); ?></strong>
                <p><span class="description"><?php _e("The Grab API Keys will automatically add your API Keys and activate your Extensions. You can also manually enter your API for each Extension following the steps <a href=\"http://docs.mainwp.com/enter-extensions-api-keys/\" >here</a>."); ?></span></p>
                <p>
                    <span class="extension_api_loading">
                        <input type="button" class="mainwp-upgrade-button button-primary" id="mainwp-extensions-grabkeys" value="<?php _e("Grab Api Keys", "mainwp"); ?>">
                        <i class="fa fa-spinner fa-pulse" style="display: none;"></i><span class="status hidden"></span>
                    </span>
                </p>  
                <div style="clear: both;"></div>
            </div>
        </div>
    </div> 
       <?php } ?>         
<?php } ?>    
        
        <div class="mainwp-tabs" id="mainwp-tabs">            
            <a class="nav-tab pos-nav-tab <?php if ($shownPage === '') { echo "nav-tab-active"; } ?>" href="admin.php?page=Extensions"><?php _e('Manage Extensions', 'mainwp'); ?></a>            
            <?php
            if (isset($extensions) && is_array($extensions))
            {
                foreach ($extensions as $extension)
                {
                    if ($extension['plugin'] == $shownPage)
                    {
                        ?>
                        <a class="nav-tab pos-nav-tab echo nav-tab-active" href="admin.php?page=<?php echo $extension['page']; ?>"><?php echo $extension['name']; ?></a>
                        <?php
                    }
                }
            }
            ?>
        </div>
        <div id="mainwp_wrap-inside">
        <?php
    }

    public static function renderFooter($shownPage, &$extensions)
    {
        ?>
        </div>
    </div>
        <?php
    }

    public static function render(&$extensions)
    {    
        
    $loader_url = '<i class="fa fa-spinner fa-pulse"></i>';     
    if (mainwp_current_user_can("dashboard", "manage_extensions")) { ?>
        
        <?php } ?>
    <div class="postbox">
    <div class="handlediv"><br></div>
    <h3 class="mainwp_box_title"><span><?php printf(_n('%d Installed MainWP Extension', '%d Installed MainWP Extensions', (count($extensions) == 1 ? 1 : 2), 'mainwp'), count($extensions)); ?></span></h3>


<div id="mainwp-extensions-wrap">    
    <?php if (count($extensions) == 0)  { ?>
    <div class="inside">
            <div class="mainwp_info-box-blue">
                <h3><?php _e('What are Extensions?', 'mainwp'); ?></h3>
                <?php _e('Extensions are specific features or tools created for the purpose of expanding the basic functionality of MainWP.', 'mainwp'); ?>
                <h3><?php _e('Why have Extensions?', 'mainwp'); ?></h3>
                <?php _e('The core of MainWP has been designed to provide the functions most needed by our users and minimize code bloat. Extensions offer custom functions and features so that each user can tailor their MainWP to their specific needs.', 'mainwp'); ?>
                <p><a href="https://extensions.mainwp.com/"><?php _e('Download your first extension now.', 'mainwp'); ?></a></p>
            </div>
    </div>
<?php  }  else {            
?>
<div style="background: #eee; padding: 1em .6em;">
<a class="mainwp_action left mainwp_action_down" href="#" id="mainwp-extensions-expand"><?php _e('Expand', 'mainwp'); ?></a><a class="mainwp_action right" href="#" id="mainwp-extensions-collapse"><?php _e('Collapse', 'mainwp'); ?></a>  
<?php if (mainwp_current_user_can("dashboard", "manage_extensions")) { ?>
    <div style="float: right; margin-top: -3px;"><a href="#" class="button mainwp-extensions-disable-all"><?php _e('Disable All', 'mainwp'); ?></a> <a href="#" class="button-primary mainwp-extensions-enable-all"><?php _e('Enable All', 'mainwp'); ?></a> <a href="<?php echo admin_url( 'plugin-install.php?tab=upload' ); ?>" class="mainwp-upgrade-button button-primary button"><?php _e('Install New Extension', 'mainwp'); ?></a></div><div style="clear: both;"></div>
<?php } ?>
</div>
<div id="mainwp-extensions-list">
	<?php    
	
	$user_can_manage_extensions = mainwp_current_user_can("dashboard", "manage_extensions");
	
    if (isset($extensions) && is_array($extensions))
    {
        foreach ($extensions as $extension)
        {
            if (!mainwp_current_user_can("extension", dirname($extension['slug'])))
               continue;
            $active = MainWPExtensions::isExtensionActivated($extension['plugin']);			
			$enabled = MainWPExtensions::isExtensionEnabled($extension['plugin']);
            
            $queue_status = "";
            
            if (isset($extension['apiManager']) && $extension['apiManager']) { 
                $queue_status = 'status="queue"';
            }
	?>
        <div class="mainwp-extensions-childHolder" extension_slug="<?php echo $extension['slug']; ?>" <?php echo $queue_status; ?> license-status="<?php echo $active ? "activated" : "deactivated"; ?>">
            <table style="width: 100%">
                <td class="mainwp-extensions-childIcon">
                    <?php
                    if (isset($extension['iconURI']) && ($extension['iconURI'] != ''))
                    {
                        ?><img title="<?php echo $extension['name']; ?>" src="<?php echo MainWPUtility::removeHttpPrefix($extension['iconURI']); ?>" class="mainwp-extensions-img large <?php echo ($enabled ? '' : 'mainwp-extension-icon-desaturated'); ?>" /><?php
                    }
                    else
                    {
                        ?><img title="MainWP Placeholder" src="<?php echo plugins_url('images/extensions/placeholder.png', dirname(__FILE__)); ?>" class="mainwp-extensions-img large <?php echo ($enabled ? '' : 'mainwp-extension-icon-desaturated'); ?>" /><?php
                    }
?>
                </td>
                <td valign="top">
                    <table style="width: 100%">
                        <tr>
                            <td class="mainwp-extensions-childName">
                                <?php 
                                    if (isset($extension['direct_page']) && !empty($extension['direct_page'])) { 
                                        ?>
                                        <a href="<?php echo admin_url('admin.php?page='.$extension['direct_page']); ?>" style="text-decoration: none;">
                                            <?php echo $extension['name']; ?>
                                        </a>
                                <?php } 
                                    else if (isset($extension['callback'])) { 
                                ?>
                                        <a href="<?php echo admin_url('admin.php?page='.$extension['page']); ?>" style="text-decoration: none;">    
                                            <?php echo $extension['name']; ?>
                                        </a>
                                <?php } 
                                    else {
                                            echo $extension['name'];
                                } ?>
                            </td>
                            <td class="mainwp-extensions-childVersion">V. <?php echo $extension['version']; ?></td>
                            <td class="mainwp-extensions-childActions">								
								<?php if (isset($extension['apiManager']) && $extension['apiManager']) { ?>
										<?php if ($active) { ?>										
											<a href="javascript:void(0)" class="api-status activated" ><?php _e('Activated','mainwp'); ?></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;											
										<?php } else {?>
											<a href="javascript:void(0)" class="api-status deactivated" title="Not Activated"><?php _e('Deactivated','mainwp'); ?></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;											
                                    <?php } ?>			
								<?php } ?>		
							
												
                                <?php if ($enabled) { ?>    											
									<button href="#" <?php echo $user_can_manage_extensions ? 'class="button mainwp-extensions-disable"' : 'disabled="disabled"'; ?> ><?php _e('Disable','mainwp'); ?></button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;										
								<?php } else {  ?> 
									<button <?php echo $user_can_manage_extensions ? 'class="button-primary mainwp-extensions-enable"' : 'disabled="disabled"'; ?> ><?php _e('Enable','mainwp'); ?></button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;										
								<?php } ?>
								    
								<?php if (isset($extension['apiManager']) && $extension['apiManager']) { ?>
										<?php if ($user_can_manage_extensions) { ?>																					                                  
											<?php if ($active) { ?>																					                                  
													<a href="#" class="mainwp-extensions-api-activation" style="font-size: 28px;"><i class="fa fa-lock"></i></a>											
											<?php } else {?>
													<a href="#" class="mainwp-extensions-api-activation" style="font-size: 28px;"><i class="fa fa-unlock"></i></a>										
											<?php } ?>
										<?php } else { ?>
											<?php if ($active) { ?>																					                                  
													<span style="font-size: 28px; color: #e5e5e5;"><i class="fa fa-lock"></i></span>											
											<?php } else {?>
													<span style="font-size: 28px; color: #e5e5e5;"><i class="fa fa-unlock"></i></span>										
											<?php } ?>	
										<?php } ?>			
								<?php } else { ?>
											<span style="font-size: 28px; color: #e5e5e5;"><i class="fa fa-lock"></i></span>										
								<?php } ?>		
										
								<?php if (isset($extension['direct_page']) && !empty($extension['direct_page'])) { ?>
									<a href="<?php echo admin_url('admin.php?page='.$extension['direct_page']); ?>" style="font-size: 28px;"><i class="fa fa-wrench"></i></a>
								<?php } else if (isset($extension['callback'])) { ?>
									<a href="<?php echo admin_url('admin.php?page='.$extension['page']); ?>" style="font-size: 28px;"><i class="fa fa-wrench"></i></a>
								<?php } else { ?>
									<span style="font-size: 28px; color: #e5e5e5;"><i class="fa fa-wrench"></i></span>
								<?php } ?>

								<?php if (false && $user_can_manage_extensions) { ?>
										<a href="#" class="mainwp-extensions-trash" style="font-size: 28px"><i class="fa fa-trash"></i></a>
								<?php } else { ?>
										<span style="font-size: 28px; color: #e5e5e5;"><i class="fa fa-trash"></i></span>
								<?php } ?>  
                            </td>
                        </tr>
                        <tr class="mainwp-extensions-extra mainwp-extension-description"><td colspan="3"><br/><br/><?php echo preg_replace('/\<cite\>.*\<\/cite\>/', '', $extension['description']); ?><br/><br/></td></tr>
                        <tr class="mainwp-extensions-links">
                            <td colspan="3">
                                <?php printf(__('By %s', 'mainwp'), str_replace(array("http:", "https:"), "", $extension['author'])); ?>
                                <?php echo (isset($extension['DocumentationURI']) && !empty($extension['DocumentationURI'])) ? ' | <a href="' . str_replace(array("http:", "https:"), "", $extension['DocumentationURI']) . '" target="_blank" title="' . __("Documentation", "mainwp") . '">' . __("Documentation", "mainwp") . '</a>' : ""; ?>
                                <?php echo (isset($extension['SupportForumURI']) && !empty($extension['SupportForumURI'])) ? ' | <a href="' . str_replace(array("http:", "https:"), "", $extension['SupportForumURI']) . '" target="_blank" title="' . __("Support Forum", "mainwp") . '">' . __("Support Forum", "mainwp") . '</a>' : ""; ?>
                                <?php if (isset($extension['apiManager']) && $extension['apiManager']) { ?>
                                    <?php echo ' | <a href="#" class="mainwp-extensions-api-activation" >' . __('Enter Activation API') . '</a>'; ?>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php if (isset($extension['apiManager']) && $extension['apiManager']) { ?>
                        <tr class="mainwp-extensions-api-row">
                            <td colspan="3">
                                <div class="api-row-div">
                                    <span>
                                    <input type="text" class="input api_key" placeholder="<?php echo __("API License Key", "mainwp"); ?>" value="<?php echo $extension["api_key"]; ?>"/>
                                    <input type="text" class="input api_email" placeholder="<?php echo __("API License Email", "mainwp"); ?>" value="<?php echo $extension["activation_email"]; ?>"/>
                                    <input type="button" class="button-primary mainwp-extensions-activate" value="<?php _e("Activate", "mainwp"); ?>">                            
                                    <span class="mainwp_loading"><i class="fa fa-spinner fa-pulse"></i></span>
                                    </span>
                                    <span style="float:right">
                                    <?php _e("Deactivate License Key", "mainwp"); ?>
                                    <input type="checkbox" class="mainwp-extensions-deactivate-chkbox" <?php echo $extension['deactivate_checkbox'] == 'on' ? "checked" : ""; ?>>
                                    <input type="button" class="button-primary mainwp-extensions-deactivate" value="<?php _e("Deactivate", "mainwp"); ?>">                                
                                    </span>
                                </div>
                                <span class="activate-api-status hidden"></span>
                            </td>
                        </tr>
                        <?php } ?>
                    </table>
                </td>
            </table>
        </div>

        <?php
        }
    }
        ?>

</div>
        <?php
        }
        ?>
</div></div><?php
        self::mainwpAvailableExtensions($extensions);
    }
    public static function mainwpAvailableExtensions($extensions) {
        
        $all_extensions = self::getAvailableExtensions();
              
        $installed_slugs = array();
        if (is_array($extensions)) {
            foreach($extensions as $ext) {
                $installed_slugs[] = dirname($ext['slug']);
            }
        }
        
        ?>
        <div class="postbox">
            <div class="handle"></div>
            <h3 class="mainwp_box_title"><?php _e('Available Extensions on <a href="//extensions.mainwp.com">MainWP Extensions</a>'); ?></h3>
            <div>
                <div id="mainwp-available-extensions-list">
                    <?php
                        foreach($all_extensions as $ext ) {
                            if (in_array($ext['slug'], $installed_slugs))
                                    continue;
                            $is_free = (isset($ext['free']) && $ext['free']) ? true : false;
                                ?>
                                <div class="mainwp-availbale-extension-holder <?php echo ($is_free) ? 'mainwp-free' : 'mainwp-paid'; ?>" style="clear: both;">
                                    <div class="mainwp-av-ext-icon">
                                        <img src="<?php echo $ext['img']?>" />
                                    </div>
                                    <div class="mainwp-av-ext-buttons">
                                        <?php
                                        echo $is_free ? '<span class="mainwp-price"></span>' : '';
                                        ?>                                        
                                        <a target="_blank" href="<?php echo str_replace(array("http:", "https:"), "", $ext['link']); ?>" class="button">Find Out More</a>
                                        <a target="_blank" href="<?php echo str_replace(array("http:", "https:"), "", '//extensions.mainwp.com/checkout/?add-to-cart=' . $ext['catalog_id']); ?>" class="button button-primary mainwp-upgrade-button">Order Now</a>
                                    </div>
                                    <div class="mainwp-av-ext-desciption">
                                        <h2><?php echo $ext['title']?></h2>
                                        <p><?php echo $ext['desc']?></p>
                                    </div>

                                </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php
        }
        
        public static function getAvailableExtensions() {
            return array(                
                        array(
                                'free' => true,
                                'slug' => 'advanced-uptime-monitor-extension',
                                'title' => 'MainWP Advanced Uptime Monitor',
                                'desc' => 'MainWP Extension for real-time up time monitoring.',
                                'link' => 'https://extensions.mainwp.com/product/mainwp-advanced-uptime-monitor/',
                                'img' => 'http://extensions.mainwp.com/wp-content/uploads/2013/06/Advanced-Uptime-Monitor-300x300.png',
                                'product_id' => 'Advanced Uptime Monitor Extension',
                                'catalog_id' => '218'
                            ),
                        array(
                                'slug' => 'mainwp-article-uploader-extension',
                                'title' => 'MainWP Article Uploader Extension',
                                'desc' => 'MainWP Article Uploader Extension allows you to bulk upload articles to your dashboard and publish to child sites.',
                                'link' => 'https://extensions.mainwp.com/product/mainwp-article-uploader-extension/',
                                'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/08/mainwp-article-uploader-extension.png',
                                'product_id' => 'MainWP Article Uploader Extension',
                                'catalog_id' => '15340'
                            ),
                        array(
                                'slug' => 'mainwp-backupwordpress-extension',
                                'title' => 'MainWP BackUpWordPress Extension',
                                'desc' => 'MainWP BackUpWordPress Extension combines the power of your MainWP Dashboard with the popular WordPress BackUpWordPress Plugin. It allows you to schedule backups on your child sites.',
                                'link' => 'https://extensions.mainwp.com/product/mainwp-backupwordpress-extension/',
                                'img' => 'https://extensions.mainwp.com/wp-content/uploads/2015/05/mainwp-backupwordpress-extension.png',
                                'product_id' => 'MainWP BackUpWordPress Extension',
                                'catalog_id' => '273535'
                            ),
                        array(
                            'free' => true,
                            'slug' => 'mainwp-blogvault-backup-extension',
                            'title' => 'MainWP BlogVault Backup Extension',
                            'desc' => 'MainWP BlogVault Backup Extension allows you to claim your 25% discount for the BlogVault Backup service.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-blogvault-incremental-backup-extension/',
                            'img' => 'https://extensions.mainwp.com/wp-content/uploads/2015/10/mainwp-blogvault-backup-extension.png',
                            'product_id' => 'MainWP BlogVault Backup Extension',
                            'catalog_id' => '347111'
                        ),
                        array(
                                'slug' => 'boilerplate-extension',
                                'title' => 'MainWP Boilerplate Extension',
                                'desc' => 'MainWP Boilerplate extension allows you to create, edit and share repetitive pages across your network of child sites. The available placeholders allow these pages to be customized for each site without needing to be rewritten. The Boilerplate extension is the perfect solution for commonly repeated pages such as your "Privacy Policy", "About Us", "Terms of Use", "Support Policy", or any other page with standard text that needs to be distributed across your network.',
                                'link' => 'https://extensions.mainwp.com/product/mainwp-boilerplate-extension/',
                                'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/01/boilerplate-icon-300x300.png',
                                'product_id' => 'Boilerplate Extension',
                                'catalog_id' => '1188'
                            ),
                        array(
                                'slug' => 'mainwp-branding-extension',
                                'title' => 'MainWP Branding Extension',
                                'desc' => 'The MainWP Branding extension allows you to alter the details of the MianWP Child Plugin to reflect your companies brand or completely hide the plugin from the installed plugins list.',
                                'link' => 'https://extensions.mainwp.com/product/mainwp-child-plugin-branding-extension/',
                                'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/03/mainwp-child-pllugin-branding-extension.png',
                                'product_id' => 'MainWP Branding Extension',
                                'catalog_id' => '10679'
                            ),
                        array(
                                'slug' => 'mainwp-broken-links-checker-extension',
                                'title' => 'MainWP Broken Links Checker Extension',
                                'desc' => 'MainWP Broken Links Checker Extension allows you to scan and fix broken links on your child sites. Requires the MainWP Dashboard Plugin.',
                                'link' => 'https://extensions.mainwp.com/product/mainwp-broken-links-checker-extension/',
                                'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/07/mainwp-broken-links-checker-extension.png',
                                'product_id' => 'MainWP Broken Links Checker Extension',
                                'catalog_id' => '12737'
                            ),
                         array(
                            'slug' => 'mainwp-bulk-settings-manager',
                            'title' => 'MainWP Bulk Settings Manager',
                            'desc' => 'The Bulk Settings Manager Extension unlocks the world of WordPress directly from your MainWP Dashboard.  With Bulk Settings Manager you can adjust your Child site settings for the WordPress Core and almost any WordPress Plugin or Theme.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-bulk-settings-manager-extension/',
                            'img' => 'https://extensions.mainwp.com/wp-content/uploads/2015/10/mainwp-bulk-settings-manager-extension.png',
                            'product_id' => 'MainWP Bulk Settings Manager',
                            'catalog_id' => '347704'
                        ),
                         array(
                                'free' => true,
                                'slug' => 'mainwp-clean-and-lock-extension',
                                'title' => 'MainWP Clean and Lock Extension',
                                'desc' => 'MainWP Clean and Lock Extension enables you to remove unwanted WordPress pages from your dashboard site and to control access to your dashboard admin area.',
                                'link' => 'https://extensions.mainwp.com/product/mainwp-clean-lock-extension/',
                                'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/07/mainwp-clean-and-lock-extension.png',
                                'product_id' => 'MainWP Clean and Lock Extension',
                                'catalog_id' => '12907'
                            ),                        
                        array(
                                'slug' => 'mainwp-client-reports-extension',
                                'title' => 'MainWP Client Reports Extension',
                                'desc' => 'MainWP Client Reports Extension allows you to generate activity reports for your clients sites. Requires MainWP Dashboard.',
                                'link' => 'https://extensions.mainwp.com/product/mainwp-client-reports-extension/',
                                'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/05/mainwp-client-reports-extension.png',
                                'product_id' => 'MainWP Client Reports Extension',
                                'catalog_id' => '12139'
                            ),
                        array(
                            'slug' => 'mainwp-clone-extension',
                            'title' => 'MainWP Clone Extension',
                            'desc' => 'MainWP Clone Extension is an extension for the MainWP plugin that enables you to clone your child sites with no technical knowledge required.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-clone-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/01/mainwp-clone-ext-icon.png',
                            'product_id' => 'MainWP Clone Extension',
                            'catalog_id' => '1555'
                        ),
                        array(
                            'slug' => 'mainwp-code-snippets-extension',
                            'title' => 'MainWP Code Snippets Extension',
                            'desc' => 'The MainWP Code Snippets Extension is a powerful PHP platform that enables you to execute php code and scripts on your child sites and view the output on your Dashboard. Requires the MainWP Dashboard plugin.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-code-snippets-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/04/mainwp-code-snippets-extension.png',
                            'product_id' => 'MainWP Code Snippets Extension',
                            'catalog_id' => '11196'
                        ),
                        array(
                            'slug' => 'mainwp-comments-extension',
                            'title' => 'MainWP Comments Extension',
                            'desc' => 'MainWP Comments Extension is an extension for the MainWP plugin that enables you to manage comments on your child sites.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-comments-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/01/mainwp-comments-ext-icon.png',
                            'product_id' => 'MainWP Comments Extension',
                            'catalog_id' => '1551'
                        ),
                        array(
                            'slug' => 'mainwp-favorites-extension',
                            'title' => 'MainWP Favorites Extension',
                            'desc' => 'MainWP Favorites is an extension for the MainWP plugin that allows you to store your favorite plugins and themes, and install them directly to child sites from the dashboard repository.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-favorites-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/01/mainwp-favorites-icon.png',
                            'product_id' => 'MainWP Favorites Extension',
                            'catalog_id' => '1379'
                        ),
                        array(
                            'slug' => 'mainwp-file-uploader-extension',
                            'title' => 'MainWP File Uploader Extension',
                            'desc' => 'MainWP File Uploader Extension gives you an simple way to upload files to your child sites! Requires the MainWP Dashboard plugin.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-file-uploader-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/05/mainwp-file-uploader-extension.png',
                            'product_id' => 'MainWP File Uploader Extension',
                            'catalog_id' => '11637'
                        ),
                        array(
                            'slug' => 'mainwp-google-analytics-extension',
                            'title' => 'MainWP Google Analytics Extension',
                            'desc' => 'MainWP Google Analytics Extension is an extension for the MainWP plugin that enables you to monitor detailed statistics about your child sites traffic. It integrates seamlessly with your Google Analytics account.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-google-analytics-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/01/mainwp-ga-ext-icon.png',
                            'product_id' => 'MainWP Google Analytics Extension',
                            'catalog_id' => '1554'
                        ),
                        array(
                            'free' => true,
                            'slug' => 'mainwp-inmotion-hosting-extension',
                            'title' => 'MainWP InMotion Hosting Extension',
                            'desc' => 'MainWP InMotion Hosting Extension allows you to claim your coupon for 1 year of free hosting provided by the InMotion Hosting company.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-inmotion-hosting-extension/',
                            'img' => 'https://extensions.mainwp.com/wp-content/uploads/2015/07/mainwp-inmotion-extension.png',
                            'product_id' => 'MainWP InMotion Hosting Extension',
                            'catalog_id' => '336219'
                        ),
                        array(
                            'slug' => 'mainwp-links-manager-extension',
                            'title' => 'MainWP Links Manager',
                            'desc' => 'MainWP Links Manager is an Extension that allows you to create, manage and track links in your posts and pages for all your sites right from your MainWP Dashboard.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-links-manager/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2013/08/links-manager-1.png',
                            'product_id' => 'MainWP Links Manager Extension',
                            'catalog_id' => '317'
                        ),
                        array(
                            'slug' => 'mainwp-maintenance-extension',
                            'title' => 'MainWP Maintenance Extension',
                            'desc' => 'MainWP Maintenance Extension is MainWP Dashboard extension that clears unwanted entries from child sites in your network. You can delete post revisions, delete auto draft pots, delete trash posts, delete spam, pending and trash comments, delete unused tags and categories and optimize database tables on selected child sites.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-maintenance-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/01/Maintenance-Icon-3-300x300.png',
                            'product_id' => 'MainWP Maintenance Extension',
                            'catalog_id' => '1141'
                        ),
                        array(
                            'slug' => 'mainwp-piwik-extension',
                            'title' => 'MainWP Piwik Extension',
                            'desc' => 'MainWP Piwik Extension is an extension for the MainWP plugin that enables you to monitor detailed statistics about your child sites traffic. It integrates seamlessly with your Piwik account.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-piwik-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/03/mainwp-piwik-extension.png',
                            'product_id' => 'MainWP Piwik Extension',
                            'catalog_id' => '10523'
                        ),
                        array(
                            'slug' => 'mainwp-post-dripper-extension',
                            'title' => 'MainWP Post Dripper Extension',
                            'desc' => 'MainWP Post Dripper Extension allows you to deliver posts or pages to your network of sites over a pre-scheduled period of time. Requires MainWP Dashboard plugin.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-post-dripper-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/05/mainwp-post-dripper-extension.png',
                            'product_id' => 'MainWP Post Dripper Extension',
                            'catalog_id' => '11756'
                        ),
                        array(
                            'slug' => 'mainwp-remote-backup-extension',
                            'title' => 'MainWP Remote Backups Extension',
                            'desc' => 'MainWP Remote Backup Extension is an extension for the MainWP plugin that enables you store your backups on different off site locations.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-remote-backups-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/01/mainwp-remote-backups-ext-icon.png',
                            'product_id' => 'MainWP Remote Backup Extension',
                            'catalog_id' => '1553'
                        ),
                        array(
                            'slug' => 'mainwp-rocket-extension',
                            'title' => 'MainWP Rocket Extension',
                            'desc' => 'MainWP Rocket Extension combines the power of your MainWP Dashboard with the popular WP Rocket Plugin. It allows you to mange WP Rocket settings and quickly Clear and Preload cache on your child sites.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-rocket-extension/',
                            'img' => 'https://extensions.mainwp.com/wp-content/uploads/2015/07/mainwp-rocket-extension.png',
                            'product_id' => 'MainWP Rocket Extension',
                            'catalog_id' => '335257'
                        ),
                        array(
                            'slug' => 'mainwp-spinner',
                            'title' => 'MainWP Spinner',
                            'desc' => 'MainWP Extension Plugin allows words to spun {|} when when adding articles and posts to your blogs. Requires the installation of MainWP Main Plugin.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-spinner/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2013/06/spinner-300x300.png',
                            'product_id' => 'MainWP Spinner',
                            'catalog_id' => '110'
                        ),
                        array(
                            'free' => true,
                            'slug' => 'mainwp-sucuri-extension',
                            'title' => 'MainWP Sucuri Extension',
                            'desc' => 'MainWP Sucuri Extension enables you to scan your child sites for various types of malware, spam injections, website errors, and much more. Requires the MainWP Dashboard.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-sucuri-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/03/mainwp-sucuri-extension.png',
                            'product_id' => 'MainWP Sucuri Extension',
                            'catalog_id' => '10777'
                        ),
                        array(
                            'slug' => 'mainwp-team-control',
                            'title' => 'MainWP Team Control',
                            'desc' => 'MainWP Team Control extension allows you to create a custom roles for your dashboard site users and limiting their access to MainWP features. Requires MainWP Dashboard plugin.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-team-control/',
                            'img' => 'https://extensions.mainwp.com/wp-content/uploads/2014/10/mainwp-team-control-extension.png',
                            'product_id' => 'MainWP Team Control',
                            'catalog_id' => '23936'
                        ),
                        array(
                                'free' => true,
                                'slug' => 'mainwp-updraftplus-extension',
                                'title' => 'MainWP UpdraftPlus Extension',
                                'desc' => 'MainWP UpdraftPlus Extension combines the power of your MainWP Dashboard with the popular WordPress UpdraftPlus Plugin. It allows you to quickly back up your child sites.',
                                'link' => 'https://extensions.mainwp.com/product/mainwp-updraftplus-extension/',
                                'img' => 'https://extensions.mainwp.com/wp-content/uploads/2015/04/mainwp-updraftplus-extension.png',
                                'product_id' => 'MainWP UpdraftPlus Extension',
                                'catalog_id' => '165843'
                            ),
                        array(
                            'slug' => 'mainwp-url-extractor-extension',
                            'title' => 'MainWP URL Extractor Extension',
                            'desc' => 'MainWP URL Extractor allows you to search your child sites post and pages and export URLs in customized format. Requires MainWP Dashboard plugin.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-url-extractor-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/05/mainwp-url-extractor-extension.png',
                            'product_id' => 'MainWP Url Extractor Extension',
                            'catalog_id' => '11965'
                        ),
                        array(
                            'free' => true,
                            'slug' => 'mainwp-woocommerce-shortcuts-extension',
                            'title' => 'MainWP WooCommerce Shortcuts Extension',
                            'desc' => 'MainWP WooCommerce Shortcuts provides you a quick access WooCommerce pages in your network. Requires MainWP Dashboard plugin.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-woocommerce-shortcuts-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/06/mainwp-woocommerce-shortcuts-extension.png',
                            'product_id' => 'MainWP WooCommerce Shortcuts Extension',
                            'catalog_id' => '12706'
                        ),
                        array(
                            'slug' => 'mainwp-woocommerce-status-extension',
                            'title' => 'MainWP WooCommerce Status Extension',
                            'desc' => 'MainWP WooCommerce Status provides you a quick overview of your WooCommerce stores in your network. Requires MainWP Dashboard plugin.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-woocommerce-status-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/06/mainwp-woocommerce-status-extension.png',
                            'product_id' => 'MainWP WooCommerce Status Extension',
                            'catalog_id' => '12671'
                        ),
                        array(
                            'slug' => 'mainwp-wordfence-extension',
                            'title' => 'MainWP WordFence Extension',
                            'desc' => 'The WordFence Extension combines the power of your MainWP Dashboard with the popular WordPress Wordfence Plugin. It allows you to manage WordFence settings, Monitor Live Traffic and Scan your child sites directly from your dashboard. Requires MainWP Dashboard plugin.',
                            'link' => 'https://extensions.mainwp.com/product/mainwp-wordfence-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/09/mainwp-wordfence-icon.png',
                            'product_id' => 'MainWP Wordfence Extension',
                            'catalog_id' => '19678'
                        ),
                        array(
                            'slug' => 'wordpress-seo-extension',
                            'title' => 'MainWP WordPress SEO Extension',
                            'desc' => 'MainWP WordPress SEO extension by MainWP enables you to manage all your WordPress SEO by Yoast plugins across your network. Create and quickly set settings templates from one central dashboard. Requires MainWP Dashboard plugin.',
                            'link' => 'https://extensions.mainwp.com/product/wordpress-seo-extension/',
                            'img' => 'http://extensions.mainwp.com/wp-content/uploads/2014/05/wordpress-seo-extension.png',
                            'product_id' => 'MainWP Wordpress SEO Extension',
                            'catalog_id' => '12080'
                        ),
                        
                    );
        }        
    }
