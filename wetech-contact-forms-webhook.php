<?php
defined('ABSPATH') or die('No access');

/**
 * Plugin Name:       WeTech Contact Forms Webhook
 * Description:       Compatible with Contact Form 7, Elementor forms and Gravity Forms
 * Version:           1.2
 * Requires at least: 4.6
 * Tested up to:      5.8
 * Requires PHP:      5.6
 * Author:            WeTech
 * Author URI:        https://app.wetech.co.il
 * License:           GPL v2
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

const APP_URL = 'https://app.wetech.co.il/wpWebhook';

function WT_sendData($webhook_token, $data)
{
    $data['token'] = $webhook_token;
    $headers = array(
        'Content-Type' => 'application/json'
    );
    $fields = array(
        'body' => json_encode(
            $data
        ),
        'headers' => $headers,
        'method' => 'POST',
        'data_format' => 'body'
    );
    $response = wp_remote_post(APP_URL, $fields);
    return $response;
}


add_filter('wpcf7_editor_panels', 'WT_cf7_add_panel');
add_action("wpcf7_save_contact_form", "WT_cf7_save_contact_form_details", 10, 3);
if (!empty(WPCF7_VERSION) && version_compare(WPCF7_VERSION, '5.5.3', '>=')) {
    add_filter("wpcf7_pre_construct_contact_form_properties", "WT_add_cf7_properties", 10, 2);
} else {
    add_filter("wpcf7_contact_form_properties", "WT_add_cf7_properties", 10, 2);
}


function WT_add_cf7_properties($properties)
{
    $properties["wpcf7_api_data"] = isset($properties["wpcf7_api_data"]) ? $properties["wpcf7_api_data"] : array();
    $properties["wpcf7_mapping_data"] = isset($properties["wpcf7_mapping_data"]) ? $properties["wpcf7_mapping_data"] : array();
    return $properties;
}


function WT_cf7_add_panel($panels)
{
    $integration_panel = array(
        'title' => 'Webhook',
        'callback' => 'WT_cf7_integrations'
    );
    $panels["qs-cf7-api-integration"] = $integration_panel;
    return $panels;
}


function WT_cf7_get_mail_tags($post)
{
    $tags = apply_filters('qs_cf7_collect_mail_tags', $post->scan_form_tags());

    foreach ((array)$tags as $tag) {
        $type = trim($tag['type'], ' *');
        if (empty($type) || empty($tag['name'])) {
            continue;
        } elseif (!empty($args['include'])) {
            if (!in_array($type, $args['include'])) {
                continue;
            }
        } elseif (!empty($args['exclude'])) {
            if (in_array($type, $args['exclude'])) {
                continue;
            }
        }
        $mailtags[] = $tag;
    }

    return $mailtags;
}


function WT_cf7_integrations($post)
{
    $wpcf7_api_data = $post->prop('wpcf7_api_data');
    $wpcf7_api_data["token"] = isset($wpcf7_api_data["token"]) ? $wpcf7_api_data["token"] : '';
    $wpcf7_mapping_data = $post->prop('wpcf7_mapping_data');
    $mail_tags = WT_cf7_get_mail_tags($post);
    ?>
    <h2><?php echo "Webhook"; ?></h2>
    <div class="cf7_row">
        <label for="wpcf7-sf-token">
            Webhook Token:
            <input type="text" id="wpcf7-sf-token" name="wpcf7-sf[token]" class="large-text"
                   value="<?php echo esc_attr($wpcf7_api_data["token"]); ?>"/>
        </label>
    </div>
    <?php


    ?>

    <div class="cf7_row">
        <table class="form-table" role="presentation">
            <tbody>


            <tr>
                <th>
                    <label>CRM Field "First Name" matches form field: </label>
                </th>
                <td>
                    <select name="mapping[first_name]">
                        <?php
                        foreach ($mail_tags as $mail_tag) {
                            ?>
                            <option value="<?php echo esc_attr($mail_tag->name); ?>" <?php if ($wpcf7_mapping_data["first_name"] == $mail_tag->name) {
                                echo "selected";
                            } ?> ><?php echo esc_attr($mail_tag->name); ?></option>
                            <?php
                        }
                        ?>
                        <option value="0" <?php if ($wpcf7_mapping_data["first_name"] == "0") {
                            echo "selected";
                        } ?>>None
                        </option>
                    </select>
                </td>
            </tr>


            <tr>
                <th>
                    <label>CRM Field "Last Name" matches form field: </label>
                </th>
                <td>
                    <select name="mapping[last_name]">
                        <?php
                        foreach ($mail_tags as $mail_tag) {
                            ?>
                            <option value="<?php echo esc_attr($mail_tag->name); ?>" <?php if ($wpcf7_mapping_data["last_name"] == $mail_tag->name) {
                                echo "selected";
                            } ?>><?php echo esc_attr($mail_tag->name); ?></option>
                            <?php
                        }
                        ?>
                        <option value="0" <?php if ($wpcf7_mapping_data["last_name"] == "0") {
                            echo "selected";
                        } ?>>None
                        </option>
                    </select>
                </td>
            </tr>

            <tr>
                <th>
                    <label>CRM Field "Email" matches form field:</label>
                </th>
                <td>
                    <select name="mapping[email]">
                        <?php
                        foreach ($mail_tags as $mail_tag) {
                            ?>
                            <option value="<?php echo esc_attr($mail_tag->name); ?>" <?php if ($wpcf7_mapping_data["email"] == $mail_tag->name) {
                                echo "selected";
                            } ?>><?php echo esc_attr($mail_tag->name); ?></option>
                            <?php
                        }
                        ?>
                        <option value="0" <?php if ($wpcf7_mapping_data["email"] == "0") {
                            echo "selected";
                        } ?>>None
                        </option>
                    </select>
                </td>
            </tr>


            <tr>
                <th>
                    <label>CRM Field "Phone" matches form field:</label>
                </th>
                <td>
                    <select name="mapping[phone]">
                        <?php
                        foreach ($mail_tags as $mail_tag) {
                            ?>
                            <option value="<?php echo esc_attr($mail_tag->name); ?>" <?php if ($wpcf7_mapping_data["phone"] == $mail_tag->name) {
                                echo "selected";
                            } ?>><?php echo esc_attr($mail_tag->name); ?></option>
                            <?php
                        }
                        ?>
                        <option value="0" <?php if ($wpcf7_mapping_data["phone"] == "0") {
                            echo "selected";
                        } ?>>None
                        </option>
                    </select>
                </td>
            </tr>


            <tr>
                <th>
                    <label>CRM Field "Mobile" matches form field:</label>
                </th>
                <td>
                    <select name="mapping[mobile]">
                        <?php
                        foreach ($mail_tags as $mail_tag) {
                            ?>
                            <option value="<?php echo esc_attr($mail_tag->name); ?>" <?php if ($wpcf7_mapping_data["mobile"] == $mail_tag->name) {
                                echo "selected";
                            } ?>><?php echo esc_attr($mail_tag->name); ?></option>
                            <?php
                        }
                        ?>
                        <option value="0" <?php if ($wpcf7_mapping_data["mobile"] == "0") {
                            echo "selected";
                        } ?>>None
                        </option>
                    </select>
                </td>
            </tr>


            <tr>
                <th>
                    <label>CRM Field "Gender" matches form field:</label>
                </th>
                <td>
                    <select name="mapping[gender]">
                        <?php
                        foreach ($mail_tags as $mail_tag) {
                            ?>
                            <option value="<?php echo esc_attr($mail_tag->name); ?>" <?php if ($wpcf7_mapping_data["gender"] == $mail_tag->name) {
                                echo "selected";
                            } ?>><?php echo esc_attr($mail_tag->name); ?></option>
                            <?php
                        }
                        ?>
                        <option value="0" <?php if ($wpcf7_mapping_data["gender"] == "0") {
                            echo "selected";
                        } ?>>None
                        </option>
                    </select>
                </td>
            </tr>


            <tr>
                <th>
                    <label>CRM Field "Birthday" matches form field:</label>
                </th>
                <td>
                    <select name="mapping[birthday]">
                        <?php
                        foreach ($mail_tags as $mail_tag) {
                            ?>
                            <option value="<?php echo esc_attr($mail_tag->name); ?>" <?php if ($wpcf7_mapping_data["birthday"] == $mail_tag->name) {
                                echo "selected";
                            } ?>><?php echo esc_attr($mail_tag->name); ?></option>
                            <?php
                        }
                        ?>
                        <option value="0" <?php if ($wpcf7_mapping_data["birthday"] == "0") {
                            echo "selected";
                        } ?>>None
                        </option>
                    </select>
                </td>
            </tr>


            <tr>
                <th>
                    <label>CRM Field "Address" matches form field:</label>
                </th>
                <td>
                    <select name="mapping[address]">
                        <?php
                        foreach ($mail_tags as $mail_tag) {
                            ?>
                            <option value="<?php echo esc_attr($mail_tag->name); ?>" <?php if ($wpcf7_mapping_data["address"] == $mail_tag->name) {
                                echo "selected";
                            } ?>><?php echo esc_attr($mail_tag->name); ?></option>
                            <?php
                        }
                        ?>
                        <option value="0" <?php if ($wpcf7_mapping_data["address"] == "0") {
                            echo "selected";
                        } ?>>None
                        </option>
                    </select>
                </td>
            </tr>


            <tr>
                <th>
                    <label>CRM Field "City" matches form field:</label>
                </th>
                <td>
                    <select name="mapping[city]">
                        <?php
                        foreach ($mail_tags as $mail_tag) {
                            ?>
                            <option value="<?php echo esc_attr($mail_tag->name); ?>" <?php if ($wpcf7_mapping_data["city"] == $mail_tag->name) {
                                echo "selected";
                            } ?>><?php echo esc_attr($mail_tag->name); ?></option>
                            <?php
                        }
                        ?>
                        <option value="0" <?php if ($wpcf7_mapping_data["city"] == "0") {
                            echo "selected";
                        } ?>>None
                        </option>
                    </select>
                </td>
            </tr>


            <tr>
                <th>
                    <label>CRM Field "Zip" matches form field:</label>
                </th>
                <td>
                    <select name="mapping[zip]">
                        <?php
                        foreach ($mail_tags as $mail_tag) {
                            ?>
                            <option value="<?php echo esc_attr($mail_tag->name); ?>" <?php if ($wpcf7_mapping_data["zip"] == $mail_tag->name) {
                                echo "selected";
                            } ?>><?php echo esc_attr($mail_tag->name); ?></option>
                            <?php
                        }
                        ?>
                        <option value="0" <?php if ($wpcf7_mapping_data["zip"] == "0") {
                            echo "selected";
                        } ?>>None
                        </option>
                    </select>
                </td>
            </tr>


            <tr>
                <th>
                    <label>CRM Field "Message" matches form field:</label>
                </th>
                <td>
                    <select name="mapping[message]">
                        <?php
                        foreach ($mail_tags as $mail_tag) {
                            ?>
                            <option value="<?php echo esc_attr($mail_tag->name); ?>" <?php if ($wpcf7_mapping_data["message"] == $mail_tag->name) {
                                echo "selected";
                            } ?>><?php echo esc_attr($mail_tag->name); ?></option>
                            <?php
                        }
                        ?>
                        <option value="0" <?php if ($wpcf7_mapping_data["message"] == "0") {
                            echo "selected";
                        } ?>>None
                        </option>
                    </select>
                </td>
            </tr>


            </tbody>
        </table>


        <style>
            #contact-form-editor .form-table th {
                width: 400px;
            }
        </style>

        <hr>


    </div>


    <?php
}


function WT_cf7_save_contact_form_details($contact_form)
{
    $properties = $contact_form->get_properties();
    $properties['wpcf7_api_data'] = isset($_POST["wpcf7-sf"]) ? $_POST["wpcf7-sf"] : '';
    $properties['wpcf7_mapping_data'] = isset($_POST["mapping"]) ? array_map('sanitize_text_field', $_POST["mapping"]) : '';
    $contact_form->set_properties($properties);
}


add_action('wpcf7_mail_sent', function ($cf7) {

    $wpcf7_api_data = $cf7->prop('wpcf7_api_data');
    $wpcf7_api_data["token"] = isset($wpcf7_api_data["token"]) ? $wpcf7_api_data["token"] : '';
    if (isset($wpcf7_api_data["token"]) && !empty($wpcf7_api_data["token"])) {
        $wpcf7_mapping_data = $cf7->prop('wpcf7_mapping_data');


        $field_fname = $wpcf7_mapping_data['first_name'];
        $field_lname = $wpcf7_mapping_data['last_name'];
        $field_email = $wpcf7_mapping_data['email'];
        $field_phone = $wpcf7_mapping_data['phone'];
        $field_mobile = $wpcf7_mapping_data['mobile'];
        $field_gender = $wpcf7_mapping_data['gender'];
        $field_birthday = $wpcf7_mapping_data['birthday'];
        $field_address = $wpcf7_mapping_data['address'];
        $field_city = $wpcf7_mapping_data['city'];
        $field_zip = $wpcf7_mapping_data['zip'];
        $field_message = $wpcf7_mapping_data['message'];


        $first_name = isset($_POST[$field_fname]) ? sanitize_text_field($_POST[$field_fname]) : '';
        $last_name = isset($_POST[$field_lname]) ? sanitize_text_field($_POST[$field_lname]) : '';
        $email = isset($_POST[$field_email]) ? sanitize_text_field($_POST[$field_email]) : '';
        $phone = isset($_POST[$field_phone]) ? sanitize_text_field($_POST[$field_phone]) : '';
        $mobile = isset($_POST[$field_mobile]) ? sanitize_text_field($_POST[$field_mobile]) : '';
        $gender = isset($_POST[$field_gender]) ? sanitize_text_field($_POST[$field_gender]) : '';
        $birthday = isset($_POST[$field_birthday]) ? sanitize_text_field($_POST[$field_birthday]) : '';
        $address = isset($_POST[$field_address]) ? sanitize_text_field($_POST[$field_address]) : '';
        $city = isset($_POST[$field_city]) ? sanitize_text_field($_POST[$field_city]) : '';
        $zip = isset($_POST[$field_zip]) ? sanitize_text_field($_POST[$field_zip]) : '';
        $message = isset($_POST[$field_message]) ? sanitize_text_field($_POST[$field_message]) : '';

        $array_to_send = array(
            "first_name" => $first_name,
            "last_name" => $last_name,
            "email" => $email,
            "phone" => $phone,
            "mobile" => $mobile,
            "gender" => $gender,
            "birthday" => $birthday,
            "address" => $address,
            "city" => $city,
            "zip" => $zip,
            "message" => $message
        );

        WT_sendData($wpcf7_api_data["token"], $array_to_send);

    }
});


// admin menu link
add_action('admin_menu', function () {
    add_menu_page('WeTech Webhooks', 'WeTech Webhooks', 1, 'wetech_webhooks', '', 'dashicons-admin-network', 4);
});


// admin menu link
add_action('admin_menu', 'WT_elementor_webhook_admin_menu');
function WT_elementor_webhook_admin_menu()
{
    add_options_page('WeTech Webhooks', 'WeTech Webhooks', 1, 'wetech_webhooks', 'WT_elementor_webhook_front');
}


function WT_elementor_webhook_front()
{

    if (isset($_POST['action']) && $_POST['action'] == "save_webhook_url") {
        $elementor_webhook_token = isset($_POST['elementor_webhook_token']) ? sanitize_text_field($_POST['elementor_webhook_token']) : '';
        $gravity_webhook_token = isset($_POST['gravity_webhook_token']) ? sanitize_text_field($_POST['gravity_webhook_token']) : '';
        update_option('elementor_webhook_token', $elementor_webhook_token);
        update_option('gravity_webhook_token', $gravity_webhook_token);


        $array = array();


        $mapping_first_name = isset($_POST['first_name']) ? array_map('sanitize_text_field', $_POST['first_name']) : [];
        foreach ($mapping_first_name as $mapping_key => $mapping_value) {
            $array[$mapping_key]["first_name"] = sanitize_text_field($mapping_value);

        }


        $mapping_last_name = isset($_POST['last_name']) ? array_map('sanitize_text_field', $_POST['last_name']) : [];
        foreach ($mapping_last_name as $mapping_key => $mapping_value) {
            $array[$mapping_key]["last_name"] = sanitize_text_field($mapping_value);
        }


        $mapping_email = isset($_POST['email']) ? array_map('sanitize_text_field', $_POST['email']) : [];
        foreach ($mapping_email as $mapping_key => $mapping_value) {
            $array[$mapping_key]["email"] = sanitize_text_field($mapping_value);
        }

        $mapping_phone = isset($_POST['phone']) ? array_map('sanitize_text_field', $_POST['phone']) : [];
        foreach ($mapping_phone as $mapping_key => $mapping_value) {
            $array[$mapping_key]["phone"] = sanitize_text_field($mapping_value);
        }

        $mapping_mobile = isset($_POST['mobile']) ? array_map('sanitize_text_field', $_POST['mobile']) : [];
        foreach ($mapping_mobile as $mapping_key => $mapping_value) {
            $array[$mapping_key]["mobile"] = sanitize_text_field($mapping_value);
        }

        $mapping_gender = isset($_POST['gender']) ? array_map('sanitize_text_field', $_POST['gender']) : [];
        foreach ($mapping_gender as $mapping_key => $mapping_value) {
            $array[$mapping_key]["gender"] = sanitize_text_field($mapping_value);
        }

        $mapping_birthday = isset($_POST['birthday']) ? array_map('sanitize_text_field', $_POST['birthday']) : [];
        foreach ($mapping_birthday as $mapping_key => $mapping_value) {
            $array[$mapping_key]["birthday"] = sanitize_text_field($mapping_value);
        }

        $mapping_address = isset($_POST['address']) ? array_map('sanitize_text_field', $_POST['address']) : [];
        foreach ($mapping_address as $mapping_key => $mapping_value) {
            $array[$mapping_key]["address"] = sanitize_text_field($mapping_value);
        }

        $mapping_city = isset($_POST['city']) ? array_map('sanitize_text_field', $_POST['city']) : [];
        foreach ($mapping_city as $mapping_key => $mapping_value) {
            $array[$mapping_key]["city"] = sanitize_text_field($mapping_value);
        }


        $mapping_zip = isset($_POST['zip']) ? array_map('sanitize_text_field', $_POST['zip']) : [];
        foreach ($mapping_zip as $mapping_key => $mapping_value) {
            $array[$mapping_key]["zip"] = sanitize_text_field($mapping_value);
        }

        $mapping_message = isset($_POST['message']) ? array_map('sanitize_text_field', $_POST['message']) : [];
        foreach ($mapping_message as $mapping_key => $mapping_value) {
            $array[$mapping_key]["message"] = sanitize_text_field($mapping_value);
        }


        update_option('gravity_mapping_array', json_encode($array));
    }


    $elementor_webhook_token = get_option("elementor_webhook_token");
    $gravity_webhook_token = get_option("gravity_webhook_token");
    ?>


    <div class="wrap" style="padding-bottom:300px;">
        <div style="width:100%;float:left;margin-right:30px;">


            <form action="" method="post">

                <?php
                if (is_plugin_active('elementor/elementor.php') == false) {
                    ?>
                    <div class="error notice">
                        <p>Elementor is not activated</p>
                    </div>
                    <?php
                } else {
                    ?>
                    <table class="form-table">
                        <tbody>
                        <tr>
                            <th scope="row">
                                <label for="blogdescription">Webhook Token for Elementor Forms</label>
                            </th>
                            <td>
                                <input name="elementor_webhook_token" type="text" id="elementor_webhook_token"
                                       value="<?php echo esc_attr($elementor_webhook_token); ?>" class="regular-text">
                                <p class="description">
                                    In Elementor form select field, go to ADVANCED tab and make sure the fields ID
                                    following rules:<br>
                                    1) Field "First name" has ID: «<b>First_name</b>» OR «<b>first_name</b>»<br>
                                    2) Field "Last name" has ID: «<b>Last_name</b>» OR «<b>last_name</b>»<br>
                                    3) Field "Email" has ID: «<b>Email</b>» OR «<b>email</b>»<br>
                                    4) Field "Phone" has ID: «<b>Phone</b>» OR «<b>phone</b>»<br>
                                    5) Field "Mobile" has ID: «<b>Mobile</b>» OR «<b>mobile</b>»<br>
                                    6) Field "Gender" has ID: «<b>Gender</b>» OR «<b>gender</b>»<br>
                                    7) Field "Birthday" has ID: «<b>Birthday</b>» OR «<b>birthday</b>» OR
                                    «<b>bd</b>»<br>
                                    8) Field "Address" has ID: «<b>Address</b>» OR «<b>address</b>»<br>
                                    9) Field "City" has ID: «<b>City</b>» OR «<b>city</b>»<br>
                                    10) Field "Zip" has ID: «<b>Zip</b>» OR «<b>zip</b>»<br>
                                    11) Field "Message" has ID: «<b>Message</b>» OR «<b>message</b>»<br>
                                </p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <?php
                }
                ?>




                <?php
                $gravity_mapping_data = get_option('gravity_mapping_array');
                $gravity_mapping_data_decode = json_decode($gravity_mapping_data, true);
                ?>
                <?php
                if (is_plugin_active('gravityforms/gravityforms.php')) {
                    ?>
                    <table class="form-table" role="presentation">
                        <tbody>
                        <tr>
                            <th scope="row">
                                <label for="blogdescription">Webhook Token for Gravity Forms</label>
                            </th>
                            <td>
                                <input name="gravity_webhook_token" type="text" id="gravity_webhook_token"
                                       value="<?php echo esc_attr($gravity_webhook_token); ?>" class="regular-text">
                            </td>
                        </tr>
                        </tbody>
                    </table>
                    <h3>Fields mapping settings for the Gravity Forms:</h3>
                    <?php
                    $forms = GFAPI::get_forms();
                    foreach ($forms as $forms_key => $forms_value) {
                        ?>
                        <div class="form_block">
                            <h3>Form: <b><?php echo esc_attr($forms_value['title']); ?></b></h3>
                            <table class="form-table">
                                <tbody>

                                <tr>
                                    <th>
                                        <label>CRM Field "First Name" matches form field:</label>
                                    </th>
                                    <td>
                                        <select name="first_name[<?php echo esc_attr($forms_value['id']); ?>]">
                                            <?php
                                            foreach ($forms_value['fields'] as $field_key => $field_value) {
                                                ?>
                                                <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['first_name'] == $field_value['fieldID']) {
                                                    echo 'selected';
                                                } ?>
                                                        value="<?php echo esc_attr($field_value['fieldID']); ?>"
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "disabled";
                                                        }
                                                    } ?>>
                                                    <?php echo esc_attr($field_value['label']); ?>
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "| Field group. Don't select this.";
                                                        }
                                                    } ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                            <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['first_name'] == "0") {
                                                echo 'selected';
                                            } ?> value="0">None
                                            </option>
                                        </select>
                                    </td>
                                </tr>


                                <tr>
                                    <th>
                                        <label>CRM Field "Last Name" matches form field:</label>
                                    </th>
                                    <td>
                                        <select name="last_name[<?php echo esc_attr($forms_value['id']); ?>]">
                                            <?php
                                            foreach ($forms_value['fields'] as $field_key => $field_value) {
                                                ?>
                                                <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['last_name'] == $field_value['fieldID']) {
                                                    echo 'selected';
                                                } ?>
                                                        value="<?php echo esc_attr($field_value['fieldID']); ?>"
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "disabled";
                                                        }
                                                    } ?>>
                                                    <?php echo esc_attr($field_value['label']); ?>
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "| Field group. Don't select this.";
                                                        }
                                                    } ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                            <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['last_name'] == "0") {
                                                echo 'selected';
                                            } ?> value="0">None
                                            </option>
                                        </select>
                                    </td>
                                </tr>


                                <tr>
                                    <th>
                                        <label>CRM Field "Email" matches form field:</label>
                                    </th>
                                    <td>
                                        <select name="email[<?php echo esc_attr($forms_value['id']); ?>]">
                                            <?php
                                            foreach ($forms_value['fields'] as $field_key => $field_value) {
                                                ?>
                                                <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['email'] == $field_value['fieldID']) {
                                                    echo 'selected';
                                                } ?>
                                                        value="<?php echo esc_attr($field_value['fieldID']); ?>"
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "disabled";
                                                        }
                                                    } ?>>
                                                    <?php echo esc_attr($field_value['label']); ?>
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "| Field group. Don't select this.";
                                                        }
                                                    } ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                            <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['email'] == "0") {
                                                echo 'selected';
                                            } ?> value="0">None
                                            </option>
                                        </select>
                                    </td>
                                </tr>


                                <tr>
                                    <th>
                                        <label>CRM Field "Phone" matches form field:</label>
                                    </th>
                                    <td>
                                        <select name="phone[<?php echo esc_attr($forms_value['id']); ?>]">
                                            <?php
                                            foreach ($forms_value['fields'] as $field_key => $field_value) {
                                                ?>
                                                <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['phone'] == $field_value['fieldID']) {
                                                    echo 'selected';
                                                } ?>
                                                        value="<?php echo esc_attr($field_value['fieldID']); ?>"
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "disabled";
                                                        }
                                                    } ?>>
                                                    <?php echo esc_attr($field_value['label']); ?>
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "| Field group. Don't select this.";
                                                        }
                                                    } ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                            <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['phone'] == "0") {
                                                echo 'selected';
                                            } ?> value="0">None
                                            </option>
                                        </select>
                                    </td>
                                </tr>


                                <tr>
                                    <th>
                                        <label>CRM Field "Mobile" matches form field:</label>
                                    </th>
                                    <td>
                                        <select name="mobile[<?php echo esc_attr($forms_value['id']); ?>]">
                                            <?php
                                            foreach ($forms_value['fields'] as $field_key => $field_value) {
                                                ?>
                                                <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['mobile'] == $field_value['fieldID']) {
                                                    echo 'selected';
                                                } ?>
                                                        value="<?php echo esc_attr($field_value['fieldID']); ?>"
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "disabled";
                                                        }
                                                    } ?>>
                                                    <?php echo esc_attr($field_value['label']); ?>
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "| Field group. Don't select this.";
                                                        }
                                                    } ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                            <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['mobile'] == "0") {
                                                echo 'selected';
                                            } ?> value="0">None
                                            </option>
                                        </select>
                                    </td>
                                </tr>


                                <tr>
                                    <th>
                                        <label>CRM Field "Gender" matches form field:</label>
                                    </th>
                                    <td>
                                        <select name="gender[<?php echo esc_attr($forms_value['id']); ?>]">
                                            <?php
                                            foreach ($forms_value['fields'] as $field_key => $field_value) {
                                                ?>
                                                <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['gender'] == $field_value['fieldID']) {
                                                    echo 'selected';
                                                } ?>
                                                        value="<?php echo esc_attr($field_value['fieldID']); ?>"
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "disabled";
                                                        }
                                                    } ?>>
                                                    <?php echo esc_attr($field_value['label']); ?>
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "| Field group. Don't select this.";
                                                        }
                                                    } ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                            <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['gender'] == "0") {
                                                echo 'selected';
                                            } ?> value="0">None
                                            </option>
                                        </select>
                                    </td>
                                </tr>


                                <tr>
                                    <th>
                                        <label>CRM Field "Birthday" matches form field:</label>
                                    </th>
                                    <td>
                                        <select name="birthday[<?php echo esc_attr($forms_value['id']); ?>]">
                                            <?php
                                            foreach ($forms_value['fields'] as $field_key => $field_value) {
                                                ?>
                                                <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['birthday'] == $field_value['fieldID']) {
                                                    echo 'selected';
                                                } ?>
                                                        value="<?php echo esc_attr($field_value['fieldID']); ?>"
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "disabled";
                                                        }
                                                    } ?>>
                                                    <?php echo esc_attr($field_value['label']); ?>
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "| Field group. Don't select this.";
                                                        }
                                                    } ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                            <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['birthday'] == "0") {
                                                echo 'selected';
                                            } ?> value="0">None
                                            </option>
                                        </select>
                                    </td>
                                </tr>


                                <tr>
                                    <th>
                                        <label>CRM Field "Address" matches form field:</label>
                                    </th>
                                    <td>
                                        <select name="address[<?php echo esc_attr($forms_value['id']); ?>]">
                                            <?php
                                            foreach ($forms_value['fields'] as $field_key => $field_value) {
                                                ?>
                                                <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['address'] == $field_value['fieldID']) {
                                                    echo 'selected';
                                                } ?>
                                                        value="<?php echo esc_attr($field_value['fieldID']); ?>"
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "disabled";
                                                        }
                                                    } ?>>
                                                    <?php echo esc_attr($field_value['label']); ?>
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "| Field group. Don't select this.";
                                                        }
                                                    } ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                            <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['address'] == "0") {
                                                echo 'selected';
                                            } ?> value="0">None
                                            </option>
                                        </select>
                                    </td>
                                </tr>


                                <tr>
                                    <th>
                                        <label>CRM Field "City" matches form field:</label>
                                    </th>
                                    <td>
                                        <select name="city[<?php echo esc_attr($forms_value['id']); ?>]">
                                            <?php
                                            foreach ($forms_value['fields'] as $field_key => $field_value) {
                                                ?>
                                                <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['city'] == $field_value['fieldID']) {
                                                    echo 'selected';
                                                } ?>
                                                        value="<?php echo esc_attr($field_value['fieldID']); ?>"
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "disabled";
                                                        }
                                                    } ?>>
                                                    <?php echo esc_attr($field_value['label']); ?>
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "| Field group. Don't select this.";
                                                        }
                                                    } ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                            <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['city'] == "0") {
                                                echo 'selected';
                                            } ?> value="0">None
                                            </option>
                                        </select>
                                    </td>
                                </tr>


                                <tr>
                                    <th>
                                        <label>CRM Field "Zip" matches form field:</label>
                                    </th>
                                    <td>
                                        <select name="zip[<?php echo esc_attr($forms_value['id']); ?>]">
                                            <?php
                                            foreach ($forms_value['fields'] as $field_key => $field_value) {
                                                ?>
                                                <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['zip'] == $field_value['fieldID']) {
                                                    echo 'selected';
                                                } ?>
                                                        value="<?php echo esc_attr($field_value['fieldID']); ?>"
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "disabled";
                                                        }
                                                    } ?>>
                                                    <?php echo esc_attr($field_value['label']); ?>
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "| Field group. Don't select this.";
                                                        }
                                                    } ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                            <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['zip'] == "0") {
                                                echo 'selected';
                                            } ?> value="0">None
                                            </option>
                                        </select>
                                    </td>
                                </tr>


                                <tr>
                                    <th>
                                        <label>CRM Field "Message" matches form field:</label>
                                    </th>
                                    <td>
                                        <select name="message[<?php echo esc_attr($forms_value['id']); ?>]">
                                            <?php
                                            foreach ($forms_value['fields'] as $field_key => $field_value) {
                                                ?>
                                                <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['message'] == $field_value['fieldID']) {
                                                    echo 'selected';
                                                } ?>
                                                        value="<?php echo esc_attr($field_value['fieldID']); ?>"
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "disabled";
                                                        }
                                                    } ?>>
                                                    <?php echo esc_attr($field_value['label']); ?>
                                                    <?php if ($field_value['inputs']) {
                                                        if (count($field_value['inputs']) > 1) {
                                                            echo "| Field group. Don't select this.";
                                                        }
                                                    } ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                            <option <?php if ($gravity_mapping_data_decode[$forms_value['id']]['message'] == "0") {
                                                echo 'selected';
                                            } ?> value="0">None
                                            </option>
                                        </select>
                                    </td>
                                </tr>

                                </tbody>
                            </table>
                            <style>
                                .form-table th {
                                    width: 400px;
                                }
                            </style>
                        </div>
                        <?php
                    }
                } else {
                    ?>
                    <div class="error notice">
                        <p>Gravity Forms is not activated</p>
                    </div>
                    <?php
                }
                ?>


                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                                         value="Save"></p>
                <input type="hidden" name="action" value="save_webhook_url">
            </form>
            <style>
                .form_block {
                    border: 1px solid gray;
                    padding: 20px;
                    margin-bottom: 20px;
                    width: 650px;
                }
            </style>

        </div>
    </div>
    <?php
}


/**
 * Render the "Field ID" property for Gravity Form fields
 * under the "Advanced" tab.
 *
 * @param int $position The current property position.
 */
function WT_gravity_add_field_id_to_settings($position)
{
    if (50 !== $position) {
        return;
    }
    ?>

    <li class="field_id_setting field_setting">
        <label for="field_field_id" class="section_label">
            <?php echo esc_html_x('Field ID', 'label for Gravity Forms field ID input', 'wt_webhook'); ?>
        </label>
        <input id="field_field_id" type="text" onchange="SetFieldProperty('fieldID', this.value);"/>
    </li>

    <?php
}

add_action('gform_field_advanced_settings', 'WT_gravity_add_field_id_to_settings');


/**
 * Print custom scripting for the "Field ID" property.
 */
function WT_gravity_field_id_editor_script()
{
    ?>

    <script type="text/javascript">
        /*
         * Add .field_id_setting onto the end of each field
         * type's properties.
         */
        jQuery.map(fieldSettings, function (el, i) {
            fieldSettings[i] += ', .field_id_setting';
        });

        // Populate field settings on initialization.
        jQuery(document).on('gform_load_field_settings', function (ev, field) {
            jQuery(document.getElementById('field_field_id'))
                .val(field.fieldID || '');
        });
    </script>

    <?php
}

add_action('gform_editor_js', 'WT_gravity_field_id_editor_script');


/**
 * Given a form entry, build an array of entry data keyed
 * with its field IDs.
 *
 * The resulting array will include any value from $entry
 * on a $form field with an assigned fieldID property.
 * Complex fields are handled as long as the field IDs are
 * passed as a comma-separated list and we have enough IDs
 * for each non-hidden input within a field.
 *
 * For example, if $form has a GF_Field_Name field
 * containing a first and last name, but we only provide a
 * single field ID (e.g. "name"), only the first name would
 * be saved. Instead, we want to be sure we're using field
 * IDs like "firstname, lastname" to ensure that all data
 * gets mapped.
 *
 * @param array $entry The Gravity Forms entry object.
 * @param array $form The Gravity Forms form object.
 * @return array An array of entry values from fields with
 * IDs attached.
 */
function WT_gravity_get_mapped_fields($entry, $form)
{
    $mapping = array();

    foreach ($form['fields'] as $field) {
        if (!isset($field['fieldID']) || !$field['fieldID']) {
            continue;
        }

        // Explode field IDs.
        $field_ids = explode(',', $field['fieldID']);
        $field_ids = array_map('trim', $field_ids);

        // We have a complex field, with multiple inputs.
        if (!empty($field['inputs'])) {
            foreach ($field['inputs'] as $input) {
                if (isset($input['isHidden']) && $input['isHidden']) {
                    continue;
                }

                $field_id = array_shift($field_ids);

                // If $field_id is empty, don't map this input.
                if (!$field_id) {
                    continue;
                }

                /*
                 * Finally, map this value based on the $field_id
                 * and $input['id'].
                 */
                $mapping[$field_id] = $entry[$input['id']];
            }
        } else {
            $mapping[$field_ids[0]] = $entry[$field['id']];
        }
    }

    return $mapping;
}


function WT_gravityform_webhook($entry, $form)
{
    $gravity_webhook_token = get_option("gravity_webhook_token");

    if (isset($gravity_webhook_token) && !empty($gravity_webhook_token)) {

        $mapped_fields = WT_gravity_get_mapped_fields($entry, $form);
        $form_id = $form['id'];
        $all_forms_mapping_data = get_option("gravity_mapping_array");
        $all_forms_mapping_data_decode = json_decode($all_forms_mapping_data, true);

        $this_form_data = $all_forms_mapping_data_decode[$form_id];


        $first_name = $mapped_fields[$this_form_data['first_name']];
        $last_name = $mapped_fields[$this_form_data['last_name']];
        $email = $mapped_fields[$this_form_data['email']];
        $phone = $mapped_fields[$this_form_data['phone']];
        $mobile = $mapped_fields[$this_form_data['mobile']];
        $gender = $mapped_fields[$this_form_data['gender']];
        $birthday = $mapped_fields[$this_form_data['birthday']];
        $address = $mapped_fields[$this_form_data['address']];
        $city = $mapped_fields[$this_form_data['city']];
        $zip = $mapped_fields[$this_form_data['zip']];
        $message = $mapped_fields[$this_form_data['message']];

        $array_to_send = array(
            "first_name" => $first_name,
            "last_name" => $last_name,
            "email" => $email,
            "phone" => $phone,
            "mobile" => $mobile,
            "gender" => $gender,
            "birthday" => $birthday,
            "address" => $address,
            "city" => $city,
            "zip" => $zip,
            "message" => $message
        );
        WT_sendData($gravity_webhook_token, $array_to_send);


    }
}

add_action('gform_after_submission', 'WT_gravityform_webhook', 10, 2);


add_action('elementor_pro/forms/new_record', function ($record, $handler) {

    $elementor_webhook_token = get_option("elementor_webhook_token");

    if (isset($elementor_webhook_token) && !empty($elementor_webhook_token)) {

        $raw_fields = $record->get('fields');
        $fields = [];

        $array_data = array(
            "first_name" => "",
            "last_name" => "",
            "email" => "",
            "phone" => "",
            "mobile" => "",
            "gender" => "",
            "birthday" => "",
            "address" => "",
            "city" => "",
            "zip" => "",
            "message" => ""
        );


        foreach ($raw_fields as $id => $field) {
            $fields[$id] = $field['value'];

            if ($field['id'] == 'First_name' || $field['id'] == 'first_name') {
                $first_name = sanitize_text_field($field['value']);
                $array_data["first_name"] = $first_name;
            }

            if ($field['id'] == 'Last_name' || $field['id'] == 'last_name') {
                $last_name = sanitize_text_field($field['value']);
                $array_data["last_name"] = $last_name;
            }

            if ($field['id'] == 'email' || $field['id'] == 'Email') {
                $email = sanitize_text_field($field['value']);
                $array_data["email"] = $email;
            }


            if ($field['id'] == 'phone' || $field['id'] == 'Phone') {
                $phone = sanitize_text_field($field['value']);
                $array_data["phone"] = $phone;
            }


            if ($field['id'] == 'mobile' || $field['id'] == 'Mobile') {
                $mobile = sanitize_text_field($field['value']);
                $array_data["mobile"] = $mobile;
            }

            if ($field['id'] == 'gender' || $field['id'] == 'Gender') {
                $gender = sanitize_text_field($field['value']);
                $array_data["gender"] = $gender;
            }

            if ($field['id'] == 'birthday' || $field['id'] == 'Birthday' || $field['id'] == 'bd') {
                $birthday = sanitize_text_field($field['value']);
                $array_data["birthday"] = $birthday;
            }


            if ($field['id'] == 'address' || $field['id'] == 'Address') {
                $address = sanitize_text_field($field['value']);
                $array_data["address"] = $address;
            }


            if ($field['id'] == 'city' || $field['id'] == 'City') {
                $city = sanitize_text_field($field['value']);
                $array_data["city"] = $city;
            }

            if ($field['id'] == 'zip' || $field['id'] == 'Zip') {
                $zip = sanitize_text_field($field['value']);
                $array_data["zip"] = $zip;
            }

            if ($field['id'] == 'message' || $field['id'] == 'Message') {
                $message = sanitize_text_field($field['value']);
                $array_data["message"] = $message;
            }
        }

        WT_sendData($elementor_webhook_token, $array_data);
    }
}, 10, 2);
