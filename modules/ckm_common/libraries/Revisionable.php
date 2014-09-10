<?php

/**
 * Class description.
 *
 * $Id: Revisionable.php 329 2011-06-21 03:08:23Z zhangjyr $
 *
 * @package    package_name
 * @author     UUTUU xu.ronghua
 * @copyright  (c) 2008-2010 UUTUU
 */
interface Revisionable   {
/* Methods */
  function get_all_revisions();
  function get_revisions();
  function load_revision($rev);
  function revise($operator,$model);
  function rollback($operator,$rev);
}

?>