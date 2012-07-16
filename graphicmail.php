<?php

/*

Plugin Name: GraphicMail

Plugin URI: http://www.graphicmail.com/

Description: GraphicMail subscription form

Version: 1.1

Author: GraphicMail

Author URI: http://www.graphicmail.com/

*/



require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'graphicmail.class.php';



class G_Widget extends WP_Widget

{

    function G_Widget()

    {

        $widget_ops = array('classname' => 'widget_graphicmail', 'description' => 'GraphicMail newsletter subscription form');

        $this->WP_Widget('graphicmail', 'GraphicMail', $widget_ops);

    }



    function widget($args, $instance)

    {

        extract($args, EXTR_SKIP);



        //$state = get_option('gm_off', true);		$state = get_option('gm_off');

        if($state) return;


		if(isset($_POST['list']) and $_POST['list'] == $instance['list']){
			if(!empty($_POST['g_mail']))
	
			{
	
				$mail = $_POST['g_mail'];
	
				if(preg_match('/[@.]/i', $mail))
	
				{
	
					$g_url = get_option('g_url');
	
					$g_user = get_option('g_user');
	
					$g_pass = get_option('g_pass');
	
	
	
					$gm = new GraphicMail($g_url, $g_user, $g_pass);
	
					if($gm->subscribe($mail, $instance['list']))
	
					{
	
						$msg = __('You have been subscribed');
	
					}
	
					else
	
					{
	
						$msg = __('Error subscribing you to the mailing list');
	
					}
	
				}
	
				else
	
				{
	
					$msg = __('Invalid email address');
	
				}
	
			}elseif(isset($_POST['g_mail'])){
				$msg = __('Please fill email address.');
			}
		}


        echo $before_widget;

        $title = empty($instance['title'])?'&nbsp;':apply_filters('widget_title', $instance['title']);



        echo $before_title, $title, $after_title;

?>

<form action ="" method="post">
	<input type="hidden" name="list" value="<?=$instance['list']?>" />
    <p class="g-mail-address">

        <input type="text" name="g_mail" />

    </p>

    <?php if(isset($msg) and $_POST['list'] == $instance['list']) { ?>

    <p class="g-message">

        <?php echo $msg; ?>

    </p>

    <?php } ?>

    <p class="g-submit">

        <input type="submit" value="<?php _e('Subscribe'); ?>" />

    </p>

</form>

<?php

        echo $after_widget;



    }



    function update($new_instance, $old_instance)

    {

        $instance = $old_instance;

        $instance['title'] = strip_tags($new_instance['title']);

        $instance['list'] = $new_instance['list'];



        return $instance;

    }



    function form($instance)

    {

        $g_url = get_option('g_url');

		$g_user = get_option('g_user');

        $g_pass = get_option('g_pass');

        

        $gm = new GraphicMail($g_url, $g_user, $g_pass);

        $g_lists = $gm->get_lists();

        if(!is_array($g_lists))

        {

?>

<p><strong>Error while getting the mailing lists from graphicmail.com.</strong></p>

<p>Message received from graphicmail.com: <em><?php echo $g_lists; ?></em></p>

<?php

        }

        else

        {

            reset($g_lists);

        

            $instance = wp_parse_args((array)$instance, array('title'=>'Newsletter', 'list'=>@key($g_lists)));

            $title = strip_tags($instance['title']);

            $list = $instance['list'];

?>

<p>

    <label for="<?php echo $this->get_field_id('title'); ?>">Title:

        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />

    </label>

    <label for="<?php echo $this->get_field_id('list'); ?>">List:</label>

    <select class="widefat" id="<?php echo $this->get_field_id('list'); ?>" name="<?php echo $this->get_field_name('list'); ?>">

        <?php foreach($g_lists as $id=>$desc) { ?>

        <option value="<?php echo $id; ?>" <?php if($id==$list) echo 'selected="selected"' ?>><?php echo htmlspecialchars($desc); ?></option>

        <?php } ?>

    </select>

</p>

<?php

        }

    }

}

function g_register_widget()

{

    register_widget('G_Widget');

}

add_action('widgets_init', 'g_register_widget');







function g_options()

{

    ?>

<div class="wrap">

    <h2>GraphicMail options</h2>

    <?php

	$g_url = get_option('g_url', '');

    $g_user = get_option('g_user', '');

    $g_pass = get_option('g_pass', '');

    if(!empty($g_url) && !empty($g_user) && !empty($g_pass))

    {

      $gm = new GraphicMail($g_url, $g_user, $g_pass);
		
		$check_ar = $gm->check_credentials();
		
      if(!$check_ar[0])

      {

        update_option('gm_off', true);

        ?>

        <div class="g-error updated fade" style="color:#832222"><p><?=$check_ar[1]?></p></div>

        <?php

      }

      else

      {
				
        update_option('gm_off', false);

      }

    }

    ?>

    <form action="options.php" method="post">

        <?php settings_fields('graphicmail'); ?>

        <table class="form-table">

			<tr valign="top">

                <td colspan=2>Please provide the GraphicMail domain you have an account on, and the Username and Password to your account.</td>

                
            </tr>
	<tr valign="top">

                <th scope="row">URL</th>

                <td>

					<input type="text" name="g_url" value="<?php echo get_option('g_url'); ?>" style="width:350px;" /> E.g. www.graphicmail.com

                </td>

            </tr>

            <tr valign="top">

                <th scope="row">Username</th>

                <td>

                    <input type="text" name="g_user" value="<?php echo get_option('g_user'); ?>" style="width:250px;" />

                </td>

            </tr>

            <tr valign="top">

                <th scope="row">Password</th>

                <td>

                    <input type="password" name="g_pass" value="<?php echo get_option('g_pass'); ?>" />

                </td>

            </tr>

        </table>

        <p class="submit">

            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />

        </p>

    </form>

</div>

    <?php

}



function register_g_settings()

{

  register_setting('graphicmail', 'g_url');

  register_setting('graphicmail', 'g_user');

  register_setting('graphicmail', 'g_pass');

}

add_action('admin_init', 'register_g_settings');



function g_menu()

{

    add_options_page('GraphicMail', 'GraphicMail', 'administrator', 'graphicmail', 'g_options');

}

add_action('admin_menu', 'g_menu');

