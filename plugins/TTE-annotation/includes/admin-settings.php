<?php

// Register settings for API key and Base URL
function tte_glycerine_embeds_register_settings()
{
    register_setting(
        'tte_glycerine_embeds_settings_group',
        'tte_glycerine_api_key'
    );

    register_setting(
        'tte_glycerine_embeds_settings_group',
        'tte_glycerine_iaw_base_url'
    );
}
add_action('admin_init', 'tte_glycerine_embeds_register_settings');

// Admin page content (called in the admin menu)
function tte_glycerine_embeds_page()
{
?>
    <div class="wrap">
        <h1>Glycerine Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('tte_glycerine_embeds_settings_group');
            do_settings_sections('TTE_Annotation_embeds');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td>
                        <input type="text" name="tte_glycerine_api_key" value="<?php echo esc_attr(get_option('tte_glycerine_api_key')); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Glycerine Server</th>
                    <td>
                        <select name="tte_glycerine_iaw_base_url">
                            <option value="https://iaw-server.ardc-hdcl-sia-iaw.cloud.edu.au/api" <?php selected(get_option('tte_glycerine_iaw_base_url'), 'https://iaw-server.ardc-hdcl-sia-iaw.cloud.edu.au/api'); ?>>
                                Australia
                            </option>
                            <option value="https://app.glycerine.io/api" <?php selected(get_option('tte_glycerine_iaw_base_url'), 'https://server.glycerine.io/api/'); ?>>
                                International
                            </option>
                        </select>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
}
