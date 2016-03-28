<?
require "MicroTest.php";
use \PowInt\Tools\MicroTest as BaseTest;

class TestNewPet extends BaseTest {
	protected function setup() {
		$this->add('getUserData');
	}
	public function getUserData() {
		$userId = 115;
		$meta = get_metadata('user', $userId);
		$this->log("META for David Powers User 2:".print_r($meta, true));		
		//echo('Heyyyy!');
		return true;
	}	
	protected function cleanup() {
		//clean out most pet meta for adminpowers user, to simplify testing!
		$this->scrubMeta(115,0);
		$this->log('*Cleaned meta for user #118, dpowers@rezon8...');
	}	
	private function scrubMeta($userId,$numberPets) {
		$userId = $userId;
		update_user_meta( $userId, 'how_many_pets_owned', $numberPets);
		$index = $numberPets + 1;
		for($p=$index;$p<2;$p++) {
			//set info for each of the pet guardians
			update_user_meta( $userId, "pet_{$p}_name", '');
			for($g=1;$g<6;$g++) {
				$prefix = "p{$p}_guardian_{$g}_";
				$arr = array('prefix','first_name','last_name','email','mobile_phone','response');
				foreach($arr as $a) {
					//echo $prefix.$a."<br>";
					update_user_meta( $userId, $prefix.$a, '');
				}
			}
		}
	}
}

class TestNotifications extends BaseTest {
	public $notification,$form,$entry,$args;
	protected function setup() {
		$this->notification = $this->args[0];
		$this->form = $this->args[1];
		$this->entry = $this->args[2];
		$this->log("TestNotifications setup()");
		$this->log("Notification:".print_r($this->notification['name'], true)); 
		$this->add('testNotificationHelper');
	}
	protected function testNotificationHelper() {
		$this->log('running TestNotifications::testNotificationHelper');
		$n = new Notify($this->notification);
		$k = json_decode($this->entry['3']);
		$n->kill_list = $k->blocked_notifications;
		$this->debug($n->kill_list,'JSON KILL LIST');
		$n->block();
		return true;
	}
	public function debug($var,$str) {
		echo $str.': '.print_r($var, true);		
	}
	public function fglog($var,$str) {
		GFCommon::log_debug( __METHOD__ . '():'.$str.' '.print_r($var, true) );		
	}
}
class TestSubmissions extends BaseTest {
	protected function setup() {
		//$this->add('testJson');
		$this->add('addPets');
	}
	protected function addPets() {
		$this->log('running TestSubmissions->addPets','');
		$userId = 92;
		$meta = get_metadata('user', $userId);
		//$this->debug($meta,"META for David Powers:");
		$data = $this->_fakePostData();
		$pet_owner_id = SubmissionsHelper::petOwnerId($meta);	
		//get the number of pets from form, NOT meta!
		$numPets = $data['how_many_pets_owned'];
		$pets = array();
		$killArr = [];
		for($i=1;$i<($numPets+1);$i++) {
			if(SubmissionsHelper::isPetNew($i,$meta)) {
				echo "We got one!";
				//if new pet, add guardians reminders to kill list
				$killArr = array_merge($killArr,KillItem::killReminders($i));
				$this->debug($killArr,'$killArr');
			} else {
				$pets[$i] = Pet::getPet($pet_owner_id,$i,$meta);				
			}
		}
		//now we take care of pets that aren't new
		foreach($pets as $pet) {
			$newPet = Pet2::getPet($pet->petfile,$data);
			$killArr = Pet2::checkGuardianNotifications($pet,$newPet,$killArr);
		}
		$json = Notify::createJson($killArr);
		$this->debug($json,'json-kill-list');
		$_POST['input_3'] = $json;
		if(count($killArr)==11) {return true;}
		else {return false;}
	}

	protected function testJson() {
		$this->log('running TestSubmissions->testJson');
		$a = new KillItem('Guardian Request Pet 1 Guardian 1','cyborgk@gmail.com');
		$b = new KillItem('Guardian Reminder Pet 1 Guardian 1','cyborgk@gmail.com');
		$c = new KillItem('Guardian Request Pet 1 Guardian 2','cyborgk@gmail.com');
		$d = new KillItem('Guardian Reminder Pet 2 Guardian 1','cyborgk@gmail.com');
		$json = Notify::createJson(array($a,$b,$c,$d));
		$this->debug($json,'Kill Item Json Rendering.');
		return true;
	}
	protected function cleanup() {
		//clean out most pet meta for adminpowers user, to simplify testing!
		$userId = 92;
		update_user_meta( $userId, 'how_many_pets_owned', '2');
		for($p=3;$p<6;$p++) {
			//set info for each of the pet guardians
			for($g=1;$g<6;$g++) {
				$prefix = "p{$p}_guardian_{$g}_";
				$arr = array('prefix','first_name','last_name','email','mobile_phone','response');
				foreach($arr as $a) {
					//echo $prefix.$a."<br>";
					update_user_meta( $userId, $prefix.$a, '');
				}
			}

		}
		$this->log('*Cleaned meta for user #92, adminpowers...');
	}

	
	public function debug($var,$str) {
		echo $str.': '.print_r($var, true)."<br>";		
	}
	private function _fakePostData() {
		return array( 
			'how_many_pets_owned'=>3,
			'num_guardians_p1'=>1, 
			'num_guardians_p2'=>1, 
			'num_guardians_p3'=>'',
			'num_guardians_p4'=>'',
			'num_guardians_p5'=>'',
			'p1_guardian_1_email'=>'cyborgk@gmail.com',
			'p1_guardian_2_email'=>'cyborgk@gmail.com',
			'p1_guardian_3_email'=>'',
			'p1_guardian_4_email'=>'',
			'p1_guardian_5_email'=>'',
			'p2_guardian_1_email'=>'cyborgk@gmail.com',
			'p2_guardian_2_email'=>'cyborgk@gmail.com',
			'p2_guardian_3_email'=>'',
			'p2_guardian_4_email'=>'',
			'p2_guardian_5_email'=>'',
			'p3_guardian_1_email'=>'cyborgk@gmail.com',
			'p3_guardian_2_email'=>'',
			'p3_guardian_3_email'=>'',
			'p3_guardian_4_email'=>'',
			'p3_guardian_5_email'=>'',
			'p4_guardian_1_email'=>'cyborgk@gmail.com',
			'p4_guardian_2_email'=>'',
			'p4_guardian_3_email'=>'',
			'p4_guardian_4_email'=>'',
			'p4_guardian_5_email'=>'',
			'p5_guardian_1_email'=>'cyborgk@gmail.com',
			'p5_guardian_2_email'=>'',
			'p5_guardian_3_email'=>'',
			'p5_guardian_4_email'=>'',
			'p5_guardian_5_email'=>''
		);
	}
}
