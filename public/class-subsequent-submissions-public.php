<?php
require("TestSubsequentSubmissions.php");
class UserHelperII {
	const PRIMARY_NUM_KEY = "mobile_phone";
	static public function findUser($pet_owner_id) {
		$user = false;
		$query = new WP_User_Query( array( 'meta_key' => 'pet_owner_id', 'meta_value' => $pet_owner_id ) );
		if (count($query->results) == 1) {
			$user = $query->results[0];
		}
		return $user;
	}
	static public function guardianMobileKey($petNum,$guardianNum) {
		return "p{$petNum}_guardian_{$guardianNum}_mobile_phone";
	}
	static public function getGuardianNumber($userId,$petNum,$guardianNum) {
		$meta = get_metadata('user', $userId);
		$key = UserHelper::guardianMobileKey($petNum,$guardianNum);
		return $meta[$key][0];
	}
	static public function updateGuardianNumber($userId,$petNum,$guardianNum,$newNum) {
		$key = UserHelper::guardianMobileKey($petNum,$guardianNum);
		update_user_meta( $userId, $key, $newNum );
		return UserHelper::getGuardianNumber($userId,$petNum,$guardianNum);
	}
}
class KillItem {
	public $name, $to;
	public function __construct($name,$to) {
		$this->name = $name;
		$this->to = $to;
	}
	static public function killReminders($petnum) {
		$arr = array();
		for($g=1;$g<6;$g++) {
			$arr[] = new KillItem("Guardian Reminder Pet {$petnum} Guardian $g",'cyborgk@gmail.com');
		}
		return $arr;
	}	
}
class Notify {
	public $kill_list;
	public function __construct($notification) {
		$this->notification = $notification;
	}
	public function matches($no) {
		return ($this->notification['name'] == $no->name);
	}
	public function block() {
		foreach ($this->kill_list as $kill_item) {
			if($this->matches($kill_item)) {
				add_filter( 'gform_pre_send_email', array( $this, 'abort_next_notification' ) );
				break;
			}
		}
	}
	public function abort_next_notification( $args ) {
		remove_filter( 'gform_pre_send_email', array( $this, 'abort_next_notification' ) );
		$args['abort_email'] = true;
		return $args;
	}	
	static public function createJson($kill_array) {
		$obj = new stdClass();
		$obj->blocked_notifications = $kill_array;
		return(json_encode($obj));
	}
}
class SubmissionsHelper {
	static public function numberPets($meta) {
		return SubmissionsHelper::meta($meta,'how_many_pets_owned');
	}
	static public function petOwnerId($meta) {
			return rgar(rgar($meta,'pet_owner_id'),0);
	}
	static public function meta($meta,$field) {
		return rgar(rgar($meta,$field),0);
	}
	static public function isPetNew($petnum,$meta) {
		$metaname = SubmissionsHelper::meta($meta,"pet_{$petnum}_name");
		//is pet name in the user meta?
		return ($metaname=="");
	}
	static public function isGuardianNew() {
		return false;
	}
	static public function hasGuardianResponded() {
		return false;
	}

}

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://rezon8.net/
 * @since      1.0.0
 *
 * @package    Subsequent_Submissions
 * @subpackage Subsequent_Submissions/public
 */

class Pet2 {
	public $guardians, $petfile;
	public function __construct( $petfile ) {
		$this->petfile = $petfile;
	}
	public function setGuardian($guardianNum,$email) {
		$this->guardians[$guardianNum] = new Guardian2($email);
	}
	static public function post2Data($post) {
		$data = [];
		$data['how_many_pets_owned'] = (int) rgar($post,'input_59');
		$data['num_guardians_p1'] = $post["input_114"];
		$data['num_guardians_p2'] = $post["input_220"];
		$data['num_guardians_p3'] = $post["input_223"];
		$data['num_guardians_p4'] = $post["input_219"];
		$data['num_guardians_p5'] = $post["input_221"];
		$formFields = array(
			'p1_guardian_1_email'=>'112','p1_guardian_2_email'=>'118',
			'p1_guardian_3_email'=>'122','p1_guardian_4_email'=>'126',
			'p1_guardian_5_email'=>'131','p2_guardian_1_email'=>'135',
			'p2_guardian_2_email'=>'137','p2_guardian_3_email'=>'140',
			'p2_guardian_4_email'=>'143','p2_guardian_5_email'=>'146',
			'p3_guardian_1_email'=>'151','p3_guardian_2_email'=>'154',
			'p3_guardian_3_email'=>'157','p3_guardian_4_email'=>'160',
			'p3_guardian_5_email'=>'163','p4_guardian_1_email'=>'167',
			'p4_guardian_2_email'=>'170','p4_guardian_3_email'=>'173',
			'p4_guardian_4_email'=>'176','p4_guardian_5_email'=>'179',
			'p5_guardian_1_email'=>'183','p5_guardian_2_email'=>'186',
			'p5_guardian_3_email'=>'189','p5_guardian_4_email'=>'192',
			'p5_guardian_5_email'=>'195'
		);
		foreach($formFields as $key => $id) {
			$data[$key] = $post["input_$id"];
		}
		return $data;
	}
	static public function petfilePost2Data($petNum,$post) {
		$formFields = array(
			"p{$petNum}_guardian_1_email"=>'167',
			"p{$petNum}_guardian_2_email"=>'173',
			"p{$petNum}_guardian_3_email"=>'177',
			"p{$petNum}_guardian_4_email"=>'182',
			"p{$petNum}_guardian_5_email"=>'185'
		);
		foreach($formFields as $key => $id) {
			$data[$key] = $post["input_$id"];
		}
		return $data;
	}
	static public function data2Pets($data) {
		for($p=1;$p<$data['how_many_pets_owned'];$p++) {
		}
		return $pets;
	}
	static public function getPet($petNum,$data) {
		$pet = new Pet2($petNum);
		$num = (int) $data["num_guardians_p{$petNum}"];
		//set info for each of the pet guardians
		for($g=1;$g<6;$g++) {
			$f = "p{$petNum}_guardian_{$g}_email";
			$pet->setGuardian($g,$data[$f]);
		}
		return $pet;
	}
	static public function checkGuardianNotifications($pet,$newPet,$killArr) {
		$g = 1;
		foreach ($pet->guardians as $guardian) {
			if($guardian->email != "") {
				//echo $guardian->email."<br>";
				GFCommon::log_debug( __METHOD__ . '(): checking Guardian '.print_r($guardian->email, true) );
				if($guardian->response=="") {
					//Add new guardian request to the kill list
					$killArr[] = new KillItem("Guardian Request Pet {$pet->petfile} Guardian $g",'');

				} else {
					//Add new guardian request to the kill list
					$killArr[] = new KillItem("Guardian Request Pet {$pet->petfile} Guardian $g",'');
					//Add guardian reminder to the kill list
					$killArr[] = new KillItem("Guardian Reminder Pet {$pet->petfile} Guardian $g",'');
				}
			} else {
				//if they there is a guardian email in form but not meta, guardian is new
				$newGuardian = $newPet->guardians[($g)];
				if ($newGuardian->email != '') {
					//Add guardian reminder to the kill list
					$killArr[] = new KillItem("Guardian Reminder Pet {$pet->petfile} Guardian $g",'');
				}
			}
			$g++;
		} 
		return $killArr;		
	}

}
class Guardian2 {
	public $email;
	public function __construct( $email ) {
		$this->email = $email;
	}
}
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Subsequent_Submissions
 * @subpackage Subsequent_Submissions/public
 * @author     David Powers <cyborgk@gmail.com>
 */
class Subsequent_Submissions_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	public function filter_add_pets($form) {
		$user = wp_get_current_user();
		$meta = get_metadata('user', $user->ID);
		$data = Pet2::post2Data($_POST);
		//print_r($data);
		$pet_owner_id = SubmissionsHelper::petOwnerId($meta);	
		//get the number of pets from form, NOT meta!
		$numPets = (int) rgar($_POST,'input_59');
		$pets = array();
		$killArr = [];
		for($i=1;$i<($numPets+1);$i++) {
			if(SubmissionsHelper::isPetNew($i,$meta)) {
				echo "We got one!";
				//if new pet, add guardians reminders to kill list
				$killArr = array_merge($killArr,KillItem::killReminders($i));
			} else {
				$pets[$i] = Pet::getPet($pet_owner_id,$i,$meta);				
			}
		}
		//now we take care of pets that aren't new
		foreach($pets as $pet) {
			$newPet = Pet2::getPet($pet->petfile,$data);
			//print_r($newPet);
			$killArr = Pet2::checkGuardianNotifications($pet,$newPet,$killArr);
		}
		$json = Notify::createJson($killArr);
		$_POST['input_239'] = $json;
		GFCommon::log_debug( __METHOD__ . '(): logging Add Pet json-kill-list: '.print_r($json, true) );
	}

	public function add_pet_notification($notification, $form, $entry) {
		$this->filter_notifications('239',$notification,$entry);
		return $notification;
	}
	public function filter_petfile1($form) {
		$this->filter_petfile(1,205);
	}
	public function filter_petfile2($form) {
		$this->filter_petfile(2,205);
	}
	public function filter_petfile3($form) {
		$this->filter_petfile(3,205);
	}
	public function filter_petfile4($form) {
		$this->filter_petfile(4,205);
	}
	public function filter_petfile5($form) {
		$this->filter_petfile(5,205);
	}			
	public function petfile1_notification($notification, $form, $entry) {
		$this->filter_notifications('205',$notification,$entry);
		return $notification;
	}
	public function filter_petfile($petNum,$hiddenFieldId) {
		$user = wp_get_current_user();
		$meta = get_metadata('user', $user->ID);
		GFCommon::log_debug( __METHOD__ . '(): logging Meta from Petfile Form: '.print_r($meta, true) );
		$pet_owner_id = SubmissionsHelper::petOwnerId($meta);	
		$data = Pet2::petfilePost2Data($petNum,$_POST);
		GFCommon::log_debug( __METHOD__ . '(): logging Parsed Data from Petfile Form: '.print_r($data, true) );
		$killArr = [];
		if(SubmissionsHelper::isPetNew($petNum,$meta)) {
			$killArr = array_merge($killArr,KillItem::killReminders($petNum));
		} else {
			$pet = Pet::getPet($pet_owner_id,$petNum,$meta);			
			GFCommon::log_debug( __METHOD__ . '(): Pet Object from Petfile Form: '.print_r($pet, true) );
			$newPet = Pet2::getPet($pet->petfile,$data);
			GFCommon::log_debug( __METHOD__ . '(): Pet2 Object from Petfile Form: '.print_r($newPet, true) );
			$killArr = Pet2::checkGuardianNotifications($pet,$newPet,$killArr);
		}
		$json = Notify::createJson($killArr);
		$key = "input_$hiddenFieldId";
		$_POST[$key] = $json;
		GFCommon::log_debug( __METHOD__ . '(): logging Add Pet json-kill-list: '.print_r($json, true) );
	}
	public function filter_notifications($field,$notification,$entry) {
		$n = new Notify($notification);
		$k = json_decode($entry[$field]);
		$n->kill_list = $k->blocked_notifications;
		//print_r($n);
		//$elog = print_r($n->kill_list, true);
		//GFCommon::log_debug( __METHOD__ . '(): UPDATE PET & GUARDIAN NOTIFICATION '.$elog );
		$n->block();

	}

	public function test_submission($f) {
		$test = new TestSubmissions();
		echo $test->log;
	}

	public function test_notification($notification, $form, $entry) {
		$test = new TestNotifications(array($notification,$form,$entry));
		echo $test->log;
		return $notification;
	}
	public function test_notification2($notification, $form, $entry) {
		$elog = print_r($notification, true);
		GFCommon::log_debug( __METHOD__ . '(): UPDATE PET & GUARDIAN NOTIFICATION '.$elog );
		return $notification;
	}
	public function add_notification_filter( $form ) {
		add_filter( 'gform_notification', array( $this, 'evaluate_notification_conditional_logic' ), 10, 3 );
		return $form;
	}
	public function add_manual_notification_event( $events ) {
		$entries = GFAPI::get_entries( '62' );
		$events['manual'] = __( 'Subsequent Submission' );
		return $events;
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Subsequent_Submissions_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Subsequent_Submissions_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/subsequent-submissions-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Subsequent_Submissions_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Subsequent_Submissions_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/subsequent-submissions-public.js', array( 'jquery' ), $this->version, false );

	}

}
