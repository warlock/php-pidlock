<?php
class PidLock {
  private $name;
  public function __construct($nid) {
    $nid=isset($nid)?$nid:"default";
    $this->name = "/var/lock/pidlock-".$nid.".lock";
    $this->ttl  = 1800;
  }
  public function make() {
    $f_id=fopen($this->name,'w+');
    fwrite($f_id,posix_getpid());
    fclose($f_id);
  }

  public function check() {
    $start = 0;
    $end = 24;
    $hour = date("H") * 1;
    if(!($hour >= $start && $hour < $end)) { return false; }

    if( file_exists($this->name) )
    {
      $mod=filemtime($this->name);

      if(time()-$mod <= $this->ttl )
      {
        return false;
      }

      $pid=@file_get_contents($this->name);
      exec("kill $pid");
      @unlink($this->name);
    }

    return true;
  }

  public function kill() {
    if(file_exists($this->name)) $ok = unlink($this->name);
    else return true;
    if(!$ok) exec("rm -f $this->name");
    $ok = !file_exists($this->name);
  }
}

?>
