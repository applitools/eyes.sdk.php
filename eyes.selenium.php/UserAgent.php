<?php

namespace Applitools\Selenium;

use Applitools\ArgumentGuard;
use Exception;

class UserAgent
{
    const MAJOR_MINOR = "(?<major>[^ .;_)]+)[_.](?<minor>[^ .;_)]+)";
    const PRODUCT = "#(?:(?<product>%s)/" . self::MAJOR_MINOR . ")#";

    // Browser Regexes
    private static $VALUES_FOR_BROWSER_REGEX_EXCEPT_IE = ["Opera", "Chrome", "Safari", "Firefox", "Edge"];

    const IE_BROWSER_REGEX = "#(?:MS(?<product>IE) " . self::MAJOR_MINOR . ")#";

    private $OS;
    private $OSMajorVersion;
    private $OSMinorVersion;
    private $Browser;
    private $BrowserMajorVersion;
    private $BrowserMinorVersion;

    private static function getBrowserRegexes()
    {
        $browserRegexes = [];

        for ($i = 0; $i < count(self::$VALUES_FOR_BROWSER_REGEX_EXCEPT_IE); $i++) {
            $browser = self::$VALUES_FOR_BROWSER_REGEX_EXCEPT_IE[$i];
            $browserRegexes[$i] = sprintf(self::PRODUCT, $browser);
        }

        // Last pattern is IE
        $browserRegexes[] = self::IE_BROWSER_REGEX;

        return $browserRegexes;
    }

    //private static List<HashMap.Entry<String, String>> noHeaders = new LinkedList<>();

    private static $VERSION_REGEX;

    private static $OS_REGEXES = [
        "#(?:(?<os>Windows) NT " . self::MAJOR_MINOR . ")#",
        "#(?:(?<os>Windows XP))#",
        "#(?:(?<os>Windows 2000))#",
        "#(?:(?<os>Windows NT))#",
        "#(?:(?<os>Windows))#",
        "#(?:(?<os>Mac OS X) " . self::MAJOR_MINOR . ")#",
        "#(?:(?<os>Android) " . self::MAJOR_MINOR . ")#",
        "#(?:(?<os>CPU(?: i[a-zA-Z]+)? OS) " . self::MAJOR_MINOR . ")#",
        "#(?:(?<os>Mac OS X))#",
        "#(?:(?<os>Mac_PowerPC))#",
        "#(?:(?<os>Linux))#",
        "#(?:(?<os>CrOS))#",
        "#(?:(?<os>SymbOS))#"];

    private static $HIDDEN_IE_REGEX = "#(?:(?:rv:" . self::MAJOR_MINOR . "\\) like Gecko))#";

    private static $EDGE_REGEX;

    /** @var bool */
    private static $initiated = false;

    /**
     * @param string $userAgent User agent string to parse
     * @param bool $unknowns Whether to treat unknown products as {@code UNKNOWN} or throw an exception.
     * @return UserAgent A representation of the user agent string.
     * @throws Exception
     */
    public static function ParseUserAgentString($userAgent, $unknowns)
    {
        ArgumentGuard::notNull($userAgent, "userAgent");

        if (!self::$initiated) {
            self::$VERSION_REGEX = sprintf(self::PRODUCT, "Version");
            self::$EDGE_REGEX = sprintf(self::PRODUCT, "Edge");
            self::$initiated = true;
        }

        $userAgent = trim($userAgent);
        $result = new UserAgent();

        // OS
        $oss = [];
        $matchers = [];

        foreach (self::$OS_REGEXES as $osRegex) {
            if (preg_match($osRegex, $userAgent, $matches)) {
                $matchers[] = $matches;
                break;
            }
        }

        foreach ($matchers as $m) {
            $os = $m["os"];
            if ($os != null) {
                $oss[strtolower($os)] = $m;
            }
        }

        $osmatch = null;
        if (count($matchers) == 0) {
            if ($unknowns) {
                $result->OS = OSNames::Unknown;
            } else {
                throw new Exception("Unknown OS: $userAgent");
            }
        } else {
            if (count($oss) > 1 && array_key_exists("android", $oss)) {
                $osmatch = $oss["android"];
            } else {
                $osmatch = array_values($oss)[0];
            }

            $result->OS = $osmatch["os"];
            if (isset($osmatch['major'])) {
                $result->OSMajorVersion = $osmatch['major'];
            }
            if (isset($osmatch['minor'])) {
                $result->OSMinorVersion = $osmatch['minor'];
            }
        }

        // OS Normalization
        if (substr($result->OS, 0, 3) === "CPU") {
            $result->OS = OSNames::IOS;
        } else if ($result->OS == "Windows XP") {
            $result->OS = OSNames::Windows;
            $result->OSMajorVersion = "5";
            $result->OSMinorVersion = "1";
        } else if ($result->OS == "Windows 2000") {
            $result->OS = OSNames::Windows;
            $result->OSMajorVersion = "5";
            $result->OSMinorVersion = "0";
        } else if ($result->OS == "Windows NT") {
            $result->OS = OSNames::Windows;
            $result->OSMajorVersion = "4";
            $result->OSMinorVersion = "0";
        } else if ($result->OS == "Mac_PowerPC") {
            $result->OS = OSNames::Macintosh;
        } else if ($result->OS == "CrOS") {
            $result->OS = OSNames::ChromeOS;
        }

        // Browser
        $browserOK = false;

        foreach (self::getBrowserRegexes() as $browserRegex) {
            preg_match($browserRegex, $userAgent, $browserMatches);
            if (count($browserMatches) > 0) {
                $result->Browser = $browserMatches["product"];
                $result->BrowserMajorVersion = $browserMatches["major"];
                $result->BrowserMinorVersion = $browserMatches["minor"];
                $browserOK = true;
                break;
            }
        }

        if ($result->OS == OSNames::Windows) {
            preg_match(self::$EDGE_REGEX, $userAgent, $edgeMatch);
            if (count($edgeMatch) > 0) {
                $result->Browser = BrowserNames::Edge;
                $result->BrowserMajorVersion = $edgeMatch["major"];
                $result->BrowserMinorVersion = $edgeMatch["minor"];
            }

            // IE11 and later is "hidden" on purpose.
            // http://blogs.msdn.com/b/ieinternals/archive/2013/09/21/
            //   internet-explorer-11-user-agent-string-ua-string-sniffing-
            //   compatibility-with-gecko-webkit.aspx
            preg_match(self::$HIDDEN_IE_REGEX, $userAgent, $iematch);
            if (count($iematch) > 0) {
                $result->Browser = BrowserNames::IE;
                $result->BrowserMajorVersion = $iematch["major"];
                $result->BrowserMinorVersion = $iematch["minor"];

                $browserOK = true;
            }
        }

        if (!$browserOK) {
            if ($unknowns) {
                $result->Browser = "Unknown";
            } else {
                throw new Exception("Unknown browser: $userAgent");
            }
        }

        // Explicit browser version (if available)
        preg_match(self::$VERSION_REGEX, $userAgent, $versionMatch);
        if (count($versionMatch)) {
            $result->BrowserMajorVersion = $versionMatch["major"];
            $result->BrowserMinorVersion = $versionMatch["minor"];
        }

        return $result;
    }

    public function getBrowser()
    {
        return $this->Browser;
    }

    public function getBrowserMajorVersion()
    {
        return $this->BrowserMajorVersion;
    }

    public function getBrowserMinorVersion()
    {
        return $this->BrowserMinorVersion;
    }

    public function getOS()
    {
        return $this->OS;
    }

    public function getOSMajorVersion()
    {
        return $this->OSMajorVersion;
    }

    public function getOSMinorVersion()
    {
        return $this->OSMinorVersion;
    }

}