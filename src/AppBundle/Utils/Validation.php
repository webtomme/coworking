<?php
namespace AppBundle\Utils;
use Symfony\Component\Config\Definition\Exception\Exception;
class Validation{
	private $em = null;
	function __construct($em) {
		$this->em = $em->getConnection();
  }
	/**
	 * Check if member has active package or not,
	 * or this member belongs to a group which have active package
	 * @return empty string or error string
	 */
	function checkMemberPackage($memberid){
		// Has active package

		if ($row = $this->em->fetchAssoc('SELECT packageid FROM member_package WHERE memberid = ? AND 1 = active', array($memberid))){
			if ($package_name = $this->em->fetchColumn('SELECT name FROM package WHERE id = '. $row['packageid'])){
				return "Khách hàng này đang dùng gói {$package_name}, đóng gói hiện tại trước khi đăng kí gói mới.";
			}
		}
		// Belong a group?
		if ($row = $this->em->fetchAssoc('SELECT groupid FROM group_member WHERE memberid = ?', array($memberid))){
			// group has active package?
			if ($group_package = $this->em->fetchAssoc('SELECT packageid FROM group_package WHERE 1 = active AND groupid = '. $row['groupid'])){
				if ($package_name = $this->em->fetchColumn('SELECT name FROM package WHERE id = '. $group_package['packageid'])){
					return "Khách hàng này nằm trong một nhóm đang dùng gói {$package_name}, đóng gói hiện tại trước khi đăng kí gói mới";
				}
			}
		}
		return '';
	}
	/**
	 * Get current active package for a member
	 */
	function getMemberPackage($memberid){
		$ret = array();
		if ($row = $this->em->fetchAssoc('SELECT mp.id, mp.efftoextend,mp.price, mp.maxhours, mp.maxdays, packageid, p.name AS packagename, mp.efffrom, mp.effto FROM member_package mp LEFT JOIN package p ON mp.packageid = p.id WHERE memberid = ? AND 1 = mp.active', array($memberid))){
		    // remainder, so tien con du?
		    $row['remain'] = 0;
		    if (0 < $row['maxhours']){
		        // Goi tinh gio, se tinh toan so gio da dung
		        $used_minutes = $this->getUsedHours($row['id']);

		        if ($used_minutes) {
		            // so tien tuong ung
		            $fee = $row['price'] / ($row['maxhours'] * 60) * $used_minutes;
		            // so du
		            $row['remain'] = $row['price'] - $fee;
		        }
		    } else {
		        // Goi tinh ngay, count up to today
		        $today = mktime(0,0,0);
		        $diff = max(0, $today - $row['efffrom'])/ 86400;// convert second to day
		        $fee = $row['price'] / $row['maxdays'] * $diff;
		        // so du
	            $row['remain'] = $row['price'] - $fee;
		    }
			$ret = $row;
		}
		return $ret;
	}
	/**
	 * Get used hours of member in a package
	 * @return used hours in minutes
	 */
	function getUsedHours($member_packageid){
	    $retval = 0;
	    $log = $this->em->fetchAll('SELECT checkin, checkout FROM customer_timelog WHERE isvisitor = 0 AND memberpackageid = ?', array($member_packageid));
	    if ($log){
	        foreach($log as $check){
	            if ($check['checkout']){
	                $retval += max(0, $check['checkout'] - $check['checkin']) / 60;// Convert second to minute
	            }
	        }
	    }
	    return $retval;
	}
	function extendMemberPackage($memberid, $extend_day, $amount){
	    if ($current_package = $this->getMemberPackage($memberid)){
	        // extend
	        $this->em->executeUpdate('UPDATE member_package SET efftoextend = ? WHERE active = 1 AND memberid = ?', array($extend_day, $memberid));
	        // log activity
	        $log = array(
	           'memberid' => $memberid,
	           'code' => 'giahantam',
	           'oldvalue' => $current_package['effto'],
	           'newvalue' => $extend_day,
	           'createdtime' => time(),
	           'amount' => (int)$amount
	        );
	        $this->em->insert('customer_activity', $log);
	    }

	}

	/**
   * Extend package for a group.
	 */
	function extendGroupPackage($groupid, $extend_day, $amount){
		$effto = $this->em->fetchAssoc('SELECT effto FROM group_package WHERE groupid = ? AND 1 = active', array($groupid));
    if (!empty($effto)){
      // extend
      $this->em->executeUpdate('UPDATE group_package SET efftoextend = ? WHERE active = 1 AND groupid = ?', array($extend_day, $groupid));
      // log activity
      $log = array(
        'groupid' => $groupid,
        'code' => 'giahantam',
        'oldvalue' => $effto['effto'],
        'newvalue' => $extend_day,
        'createdtime' => time(),
        'amount' => (int)$amount
      );
      $this->em->insert('group_activity', $log);
    }
	}

	/**
	 * Closed current active package if it has
	 */
	function closedMemberPackage($memberid){
	    $count = $this->em->executeUpdate('UPDATE member_package SET active = 0 WHERE active = 1 AND memberid = ?', array($memberid));
	    return $count;
	}

	/**
   * Check if group has active package or not
   * or this group belongs to a group which have active package
   * @return empty string or error string
	 */
	function checkGroupPackage($groupid, $efffrom = null, $effto = null){
		$row = $this->em->fetchAssoc('SELECT * FROM `group_package` WHERE groupid=?', array($groupid));
		if (!empty($row)) {
			if ($package_name = $this->em->fetchColumn('SELECT name FROM package WHERE id = '. $row['packageid'])){
				return "Nhóm này nằm trong một nhóm đang dùng gói {$package_name}, đóng gói hiện tại trước khi đăng kí gói mới";
			}
		}
		return '';
	}
}
