<?php

global $wpdb;
// This must be the same as in schema.sql (with the "wp_" prefix removed).
$DEVICES_TABLE = $wpdb->prefix . 'otf_devices';


function create_message_banner($message, $isError) {
    // FIXME display these messages better.
    return '<p>' . $message . '</p>';
}

// Renders the devices page.
function devices_page($user) {
    global $DEVICES_TABLE, $wpdb;
    $DEVICE_TYPES = ['OpenGarage'];
    ?>
	<!-- TODO move this to an external JS file. -->
	<script>
		function confirmDelete(deviceKey) {
			if ( window.confirm(`Delete device '${ deviceKey }'?`) ) {
				const form = document.createElement( "form" );
				form.style.visibility = "hidden";
				form.method = "POST";
				form.action = ".";

				const input = document.createElement( "input" );
				input.name = "removeDevice";
				input.value = deviceKey;
				form.appendChild( input );

				document.body.appendChild( form );
				form.submit();
			}
		}
	</script>
    <h3>OpenThings Devices</h3>
    <?php
    $user = wp_get_current_user();

    // Handle device creation/removal if a POST request was submitted.
    if (!empty($_POST)) {
        if ($_POST['deviceDescription'] != null && $_POST['deviceType'] != null) {
            // TODO add a limit on devices per user?
			if (in_array($_POST['deviceType'], $DEVICE_TYPES)) {
                $success = $wpdb->insert($DEVICES_TABLE, [
                    'device_description' => $_POST['deviceDescription'],
                    'device_type' => $_POST['deviceType'],
                    'device_key' => bin2hex(random_bytes(16)),
                    'user_id' => $user->ID
                ]);

                if ($success) {
                    echo create_message_banner('Added a new device to your account.', false);
                } else {
                    echo create_message_banner('An error occurred while adding a device to your account, please try again.', true);
                }
            } else {
				echo create_message_banner('Invalid device type.', true);
			}
        } else if ($_POST['removeDevice'] != null) {
            $success = $wpdb->delete($DEVICES_TABLE, [
                'device_key' => $_POST['removeDevice'],
                'user_id' => $user->ID
            ]);

            if ($success) {
                echo create_message_banner('Removed device from your account.', false);
            } else {
                echo create_message_banner('An error occurred while removing the device from your account, please try again.', true);
            }
		}
    }
    ?>

	Register your devices here so you can access them through OpenThings online services.

    <table>
        <thead>
        <tr>
            <th>Device Type</th>
            <th>OpenThings Cloud Token</th>
            <th>Device Description</th>
			<th>Actions</th>
        </tr>
        </thead>
        <?php
        $devices = $wpdb->get_results($wpdb->prepare("SELECT * FROM $DEVICES_TABLE WHERE user_id=%d", $user->ID));
        foreach ($devices as $device) {
            ?>
            <tbody>
            <tr>
                <td><?= $DEVICE_TYPES[$device->device_type]; ?></td>
                <td><?= $device->device_key; ?></td>
                <td><?= $device->device_description; ?></td>
				<!-- TODO add column for device creation date. -->
                <td><button onclick="confirmDelete('<?= esc_attr($device->device_key); ?>')">Delete</button></td>
            </tr>
            </tbody>
            <?php
        }
        unset($device);
        ?>
    </table>

    <form action="." method="POST">
        <input type="text" name="deviceDescription" placeholder="Device description">
        <select name="deviceType">
            <?php
            foreach ($DEVICE_TYPES as &$deviceType) {
                ?>
                <option value="<?= esc_attr($deviceType); ?>">
                    <?= $deviceType; ?>
                </option>
                <?php

            }
            unset($deviceType);
            ?>
        </select>
        <button type="submit">
            <span>Add new device</span>
        </button>
    </form>
    <?php
}
add_action('woocommerce_account_devices_endpoint', 'devices_page');


// Create the page /my-account/devices.
function create_devices_endpoint() {
    add_rewrite_endpoint('devices', EP_ROOT | EP_PAGES);
}
add_action('init', 'create_devices_endpoint');


// Add the "My OpenThings Devices" link to the /my-account page.
function create_devices_link($menu_links) {
    $menu_links['devices'] = 'My OpenThings Devices';
    return $menu_links;
}
add_filter('woocommerce_account_menu_items', 'create_devices_link');
