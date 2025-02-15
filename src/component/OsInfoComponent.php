<?php

namespace Component;

class OsInfoComponent
{
  private function getOS()
  {
    $osList = [
      /* -- WINDOWS -- */
      'Windows 10 (Windows NT 10.0)' => 'windows nt 10.0',
      'Windows 8.1 (Windows NT 6.3)' => 'windows nt 6.3',
      'Windows 8 (Windows NT 6.2)' => 'windows nt 6.2',
      'Windows 7 (Windows NT 6.1)' => 'windows nt 6.1',
      'Windows Vista (Windows NT 6.0)' => 'windows nt 6.0',
      'Windows Server 2003 (Windows NT 5.2)' => 'windows nt 5.2',
      'Windows XP (Windows NT 5.1)' => 'windows nt 5.1',
      'Windows 2000 sp1 (Windows NT 5.01)' => 'windows nt 5.01',
      'Windows 2000 (Windows NT 5.0)' => 'windows nt 5.0',
      'Windows NT 4.0' => 'windows nt 4.0',
      'Windows Me  (Windows 9x 4.9)' => 'win 9x 4.9',
      'Windows 98' => 'windows 98',
      'Windows 95' => 'windows 95',
      'Windows CE' => 'windows ce',
      'Windows (version unknown)' => 'windows',
      /* -- MAC OS X -- */
      'Mac OS X Beta (Kodiak)' => 'Mac OS X beta',
      'Mac OS X Cheetah' => 'Mac OS X 10.0',
      'Mac OS X Puma' => 'Mac OS X 10.1',
      'Mac OS X Jaguar' => 'Mac OS X 10.2',
      'Mac OS X Panther' => 'Mac OS X 10.3',
      'Mac OS X Tiger' => 'Mac OS X 10.4',
      'Mac OS X Leopard' => 'Mac OS X 10.5',
      'Mac OS X Snow Leopard' => 'Mac OS X 10.6',
      'Mac OS X Lion' => 'Mac OS X 10.7',
      'Mac OS X Mountain Lion' => 'Mac OS X 10.8',
      'Mac OS X Mavericks' => 'Mac OS X 10.9',
      'Mac OS X Yosemite' => 'Mac OS X 10.10',
      'Mac OS X El Capitan' => 'Mac OS X 10.11',
      'macOS Sierra' => 'Mac OS X 10.12',
      'Mac OS X (version unknown)' => 'Mac OS X',
      'Mac OS (classic)' => '(mac_powerpc)|(macintosh)',
      /* -- OTHERS -- */
      'OpenBSD' => 'openbsd',
      'SunOS' => 'sunos',
      'Ubuntu' => 'ubuntu',
      'Linux (or Linux based)' => '(linux)|(x11)',
      'QNX' => 'QNX',
      'BeOS' => 'beos',
      'OS2' => 'os/2',
      'SearchBot'=>'(nuhk)|(googlebot)|(yammybot)|(openbot)|(slurp)|(msnbot)|(ask jeeves/teoma)|(ia_archiver)'
    ];

    $useragent = htmlspecialchars($_SERVER['HTTP_USER_AGENT']);
    $useragent = strtolower($useragent);

    foreach($osList as $os=>$match) {
      if (preg_match('/' . $match . '/i', $useragent)) {
        break;
      } else {
        $os = "Unknown";
      }
    }

    return $os;
  }

  public function isWindows()
  {
    $os = self::getOS();

    if (strpos($os, 'Windows') !== false) {
      return true;
    }

    return false;
  }
}
