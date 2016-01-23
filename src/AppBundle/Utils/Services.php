<?php
namespace AppBundle\Utils;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Services{
  private $em = null;
  function __construct($em) {
    $this->em = $em->getConnection();
  }

  /**
   * Get package is used of a member
   *
   * @param int $memberid
   *
   * @return array $package
   */
  function getPackageByMemberId($memberid){
    $statement = $this->em->prepare("SELECT packageid FROM `member_package` WHERE 1=active AND memberid=:memberid");
    $statement->bindParam(':memberid', $memberid);
    $statement->execute();
    $member_package = $statement->fetchAll();
    if (!empty($member_package)){
      $statement = $this->em->prepare("SELECT * FROM `package` WHERE id=:packageid");
      $statement->bindParam(':packageid', $member_package[0]['packageid']);
      $statement->execute();
      $package = $statement->fetchAll();
      return $package[0];
    } else {
      $statement = $this->em->prepare("SELECT groupid FROM `group_member` WHERE memberid=:memberid");
      $statement->bindParam(':memberid', $memberid);
      $statement->execute();
      $group_member = $statement->fetchAll();
      if (!empty($group_member)) {
        $statement = $this->em->prepare("SELECT packageid FROM `group_package` WHERE groupid=:groupid");
        $statement->bindParam(':groupid', $group_member[0]['groupid']);
        $statement->execute();
        $group_package = $statement->fetchAll();
        if (empty($group_package)) return NULL;
        $statement = $this->em->prepare("SELECT * FROM `package` WHERE id=:packageid");
        $statement->bindParam(':packageid', $group_package[0]['packageid']);
        $statement->execute();
        $package = $statement->fetchAll();
        return $package[0];
      }
    }
  }
  function getPackageByMemberId_alt($memberid){
    if ($row = $this->em->fetchAssoc('SELECT mp.id AS memberpackageid, mp.efftoextend,mp.price, mp.maxhours, mp.maxdays, packageid, p.name, mp.efffrom, mp.effto FROM member_package mp LEFT JOIN package p ON mp.packageid = p.id WHERE memberid = ? AND 1 = mp.active', array($memberid))){
      return $row;
    }
    $statement = $this->em->prepare("SELECT groupid FROM group_member WHERE memberid=:memberid");
    $statement->bindParam(':memberid', $memberid);
    $statement->execute();
    if ($group_member = $statement->fetchColumn()){
      // user belongs to a group
      if ($row = $this->em->fetchAssoc('SELECT gp.id AS grouppackageid, gp.efftoextend,gp.price, gp.maxhours, gp.maxdays, packageid, p.name, gp.efffrom, gp.effto FROM group_package gp LEFT JOIN package p ON gp.packageid = p.id WHERE groupid = ? AND 1 = gp.active', array($group_member))){
        return $row;
      }
    }
    return array();// no package found
  }

  /**
   * Get group of member
   *
   * @param int $memberid
   *
   * @return array $group
   */
  function getGroupByMemberId($memberid){
    $statement = $this->em->prepare("SELECT groupid FROM `group_member` WHERE memberid=:memberid");
    $statement->bindParam(':memberid', $memberid);
    $statement->execute();
    $group_member = $statement->fetchAll();
    if (empty($group_member)) {
      return NULL;
    } else {
      return $group_member[0];
    }
  }

  /**
   * Get member of group
   *
   * @param int $groupid
   *
   * @return array $member
   */
  function getMembersInGroup($groupid){
    $statement = $this->em->prepare("SELECT memberid FROM `group_member` WHERE groupid=:groupid");
    $statement->bindParam(':groupid', $groupid);
    $statement->execute();
    $rows = $statement->fetchAll();
    if (empty($rows)) throw new Exception('Nhóm này không có thành viên nào.');
    $members = array();
    foreach ($rows as $row) {
      $members[] = $row['memberid'];
    }
    return $members;
  }

  /**
   * Get package by package id
   *
   * @param int $packageid
   *
   * @return array $package
   */
  function loadPackage($packageid) {
    $statement = $this->em->prepare("SELECT memberid FROM `package` WHERE id=:packageid");
    $statement->bindParam(':packageid', $packageid);
    $statement->execute();
    $rows = $statement->fetchAll();
    if (!empty($row)) {
      return $row[0];
    } else {
      return $row;
    }
  }
}
