<?php
require("TestSubsequentSubmissions.php");
/**
 * KillItem is a utility class that works with the Notify class,
 * to provide a list of notifications that should be blocked.
 */
class KillItem {
	public $name, $to;
	/**
	 * @param string $name    Name of the notification to be blocked.
	 * @param string $to      Email of the person being notified.
	 */
	public function __construct($name,$to) {
		$this->name = $name;
		$this->to = $to;
	}
	/**
	 * The killReminders method is designed for the situation when a pet is new,
	 * and all reminders for the pet should be blocked at once
	 * @param  string $petnum   Number of the petfile (1-5) for the current pet
	 * @return array            An array containing all the KillItem objects.
	 */
	static public function killReminders($petnum) {
		$arr = array();
		for($g=1;$g<6;$g++) {
			$arr[] = new KillItem("Guardian Reminder Pet {$petnum} Guardian $g",'cyborgk@gmail.com');
		}
		return $arr;
	}	
}
/**
 * Notify class is used to actually block notifications. It is initialized
 * with data from a single notification, and is designed to run on indivual 
 * notifications before they are sent. When the $kill_list attribute is set 
 * to an array of KillItem objects, and the block() method is called, it will 
 * run through the list and if the current notification matches anything in
 * the list, the notification will not be sent. 
 */
class Notify {
	public $kill_list;
	public function __construct($notification) {
		$this->notification = $notification;
	}
	public function matches($no) {
		return ($this->notification['name'] == $no->name);
	}
	public function block() {
		if(count($this->kill_list)>0) {
			foreach ($this->kill_list as $kill_item) {
				if($this->matches($kill_item)) {
					add_filter( 'gform_pre_send_email', array( $this, 'abort_next_notification' ) );
					break;
				}
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
/**
 * SubmissionsHelper class provides some simple utilities for getting meta
 * data, including checking number of pets, and whether a pet is new.
 */
class SubmissionsHelper {
	/**
	 * numberPets finds the number of pets when given the user meta data 
	 * @param  array $meta   An array containing the WP user meta data
	 * @return string        Returns value of how_many_pets_owned user meta
	 */
	static public function numberPets($meta) {
		return SubmissionsHelper::meta($meta,'how_many_pets_owned');
	}
	/**
	 * petOwnerId finds the pet owner id when given the user meta data 
	 * @param  array $meta   An array containing the WP user meta data
	 * @return string        Returns pet owner id
	 */
	static public function petOwnerId($meta) {
			return rgar(rgar($meta,'pet_owner_id'),0);
	}
	/**
	 * meta() allows one to retrieve an arbitrary field of user meta data
	 * @param  array $meta     An array containing the WP user meta data
	 * @param  string $field   Key for user meta data 
	 * @return string          Returns value of user meta if $field exists
	 */
	static public function meta($meta,$field) {
		return rgar(rgar($meta,$field),0);
	}
	/**
	 * isPetNew uses the pet name field in user meta to check if pet exists.
	 * @param  string $petnum     Which pet is it? (1-5)
	 * @param  array $meta        An array containing the WP user meta data
	 * @return boolean
	 */
	static public function isPetNew($petnum,$meta) {
		$metaname = SubmissionsHelper::meta($meta,"pet_{$petnum}_name");
		//is pet name in the user meta?
		return ($metaname=="");
	}

}

/**
 * Pet2 is a utility class that represents pet and guardian data.
 * It abstract the details of pets and guardian, making it easy to loop
 * through and test all the guardians. It also provides utility functions
 * for taking gravity form data, and converting it to use the same form
 * as the user meta data, making it easy to compare the two. 
 */

class Pet2 {
	public $guardians, $petfile;
	public function __construct( $petfile ) {
		$this->petfile = $petfile;
	}
	/**
	 * setGuardian is used when to store minimal data about the guardian with
	 * the Pet2 object, in this case just the email.
	 * @param integer $guardianNum   Which guardian is it?
	 * @param string $email          The guardian's email
	 */
	public function setGuardian($guardianNum,$email) {
		$this->guardians[$guardianNum] = new Guardian2($email);
	}
	/**
	 * post2Data converts data from the Add Pets & Guardians form
	 * and changes it into a form where it can be compared with the user
	 * meta data. 
	 * @param  $post array   Array containing post data, normally $_POST
	 * @return array         Returned array has keys named to match user meta
	 */
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
	/**
	 * petfilePost2Data lets you take form data from the update petfile
	 * and change it into a form where it can be compared with the user
	 * meta data. If you need to block notifications from some other form,
	 * you will want to right a function like this, only rewrite to use 
	 * the correct gravity form ID numbers in the $formFields array
	 * @param  string $petNum    Which petfile (1-5) is being updated
	 * @param  array $post       Form data, usually $_POST unless unit testing
	 * @return array             Array with keys that match user meta keys
	 */
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
	/**
	 * getPet is used to model pets and guardians in the submitted form data
	 * The objects created this way will be used in checkGuardianNotifications
	 * and compared against objects created from the user meta data, to check
	 * if guardians are new based on email. 
	 * @param  string $petNum   which pet (1-5)
	 * @param  array $data      user meta data, or array with same format
	 * @return Pet2 object      returns a Pet2 object with guardian info set
	 */
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
	/**
	 * checkGuardianNotifications is creates a list of blocked notifications
	 * from two versions of pet data, one from meta, and one from a form.
	 * This function is used when forms are submitted that update
	 * guardian information, for a pet that is not new.
	 * @param  Pet object $pet      (created from meta)
	 * @param  Pet2 object $newPet  (created from form data)
	 * @param  array $killArr       (list of blocked notifications)
	 * @return array                (updated list of blocked notifications)
	 */
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
/**
 * Guardian2 is a utility class that uses only the Guardian email data.
 * (Use original Guardian class for full representation of Guardian data.)
 */
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
	/**
	 * filter_add_pets runs when the Add Pets & Guardians form is submitted
	 * @param  array $form   gravity forms form data
	 * @return n/a
	 */
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
				//echo "We got one!";
				//if new pet, add guardians reminders to kill list
				$killArr = array_merge($killArr,KillItem::killReminders($i));
			} else {
				$pets[$i] = new Pet( $i, $pet_owner_id, $meta ) {
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
/**
 * add_pet_notification sets the notification blocking filter to run on the 
 * Add Pets & Guardians form, and provides the hidden field ID 239
 */
	public function add_pet_notification($notification, $form, $entry) {
		$this->filter_notifications('239',$notification,$entry);
		return $notification;
	}
/**
 * filter_petfile1 checks data submitted with petfile1, and writes json data
 * to form field 205 to be decoded in notification phase
 */
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
/**
 * petfile1_notification sets the notification blocking filter to run on the 
 * Petfile1 form, and provides the hidden field ID 205
 */
	public function petfile1_notification($notification, $form, $entry) {
		$this->filter_notifications('205',$notification,$entry);
		return $notification;
	}
	/**
	 * filter_petfile was designed to be run when petfile forms are updated,
	 * to block guardian reminders and requests as needed.
	 * @param  [type]
	 * @param  [type]
	 * @return [type]
	 */
	public function filter_petfile($petNum,$hiddenFieldId) {
		//get the current user and their metadata
		$user = wp_get_current_user();
		$meta = get_metadata('user', $user->ID);
		GFCommon::log_debug( __METHOD__ . '(): logging Meta from Petfile Form: '.print_r($meta, true) );
		$pet_owner_id = SubmissionsHelper::petOwnerId($meta);	
		//Change the submitted form data to a format that matches user meta data
		//change the following line if rewriting to grab data from other forms...
		$data = Pet2::petfilePost2Data($petNum,$_POST);
		GFCommon::log_debug( __METHOD__ . '(): logging Parsed Data from Petfile Form: '.print_r($data, true) );
		$killArr = [];
		//is it a new pet? If so, just kill all guardian reminders at once
		if(SubmissionsHelper::isPetNew($petNum,$meta)) {
			$killArr = array_merge($killArr,KillItem::killReminders($petNum));
		//otherwise, we need to create the Pet and Pet2 objects, and compare with
		//Pet2::checkGuardianNotifications()
		} else {
			$pet = new Pet( $petNum, $pet_owner_id, $meta ) {
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
	/**
	 * filter_notifications can be run on any form where you wrote 
	 * json kill list data to a hidden field, to block unwanted notifications
	 * @param  string $field        Gravity entry ID for hidden json field
	 * @param  array $notification  Gravity forms, notification data
	 * @param  array $entry         Gravity forms, form entry data
	 * @return [type]
	 */
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
		$test = new TestNewPet();
		echo $test->log;
		

		//$test = new TestSubmissions();
		//echo $test->log;

	}

	public function test_notification($notification, $form, $entry) {
		//$test = new TestNotifications(array($notification,$form,$entry));
		//echo $test->log;
		//return $notification;
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
