<?
namespace PowInt\Tools;
use stdClass;
class MicroTest {
	public $html, $log, $args;
	protected $tests, $results_arr;
	public function __construct($args=[]) {
		$this->args = $args;
		$this->html = true;
		$this->results_arr = array();
		$this->tests = array();
		$this->setup();
		$this->run();
		$this->cleanup();
		$this->log_results();
		$this->log("");
	}
	public function add($method_string) {
		array_push($this->tests, $method_string);
	}
	public function log($str) {
		if ($this->html) {$this->log .= "$str<br>";}
		else $this->log .= "$str\n";
	}
	protected function setup() {
		$this->log("MicroTest parent class setup, override in child class.");
		$this->add('dummy_pass');
		$this->add('dummy_fail');
	}
	protected function cleanup() {
		$this->log("MicroTest parent class cleanup, override in child class.");
	}
	private function run() {
		$this->log("Running tests.");
		$result_str = "";
		foreach ($this->tests as $method) {
			array_push($this->results_arr,call_user_func_array(array($this, $method),array()));
		}
	}
	private function dummy_pass() {
		$this->log("Running dummy pass!");
		return true;
	}
	private function dummy_fail() {
		$this->log("Running dummy fail!");
		return false;
	}	
	private function log_results() {
		$count = 0;
		$passed = 0;
		foreach($this->results_arr as $a) {
			$count++;
			if($a===true) {$passed++;}
		}
		$this->log("Passed $passed of $count tests.");
	}


}