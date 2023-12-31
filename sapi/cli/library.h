/**
 * -----------------------------------------------------------------------
 * Generated by make-library.php, Please DO NOT modify!
  +----------------------------------------------------------------------+
  | Swoole                                                               |
  +----------------------------------------------------------------------+
  | This source file is subject to version 2.0 of the Apache license,    |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.apache.org/licenses/LICENSE-2.0.html                      |
  | If you did not receive a copy of the Apache2.0 license and are unable|
  | to obtain it through the world-wide-web, please send a note to       |
  | license@swoole.com so we can mail you a copy immediately.            |
  +----------------------------------------------------------------------+
 */

/* $Id: cc9a4b647884865e86f1680a49554b3e80e24baa */

#ifndef SWOOLE_LIBRARY_H
#define SWOOLE_LIBRARY_H

#include "zend_exceptions.h"

#if PHP_VERSION_ID < 80000
typedef zval zend_source_string_t;
#else
typedef zend_string zend_source_string_t;
#endif

#if PHP_VERSION_ID < 80200
#define ZEND_COMPILE_POSITION_DC
#define ZEND_COMPILE_POSITION_RELAY_C
#else
#define ZEND_COMPILE_POSITION_DC , zend_compile_position position
#define ZEND_COMPILE_POSITION_RELAY_C , position
#endif

#if PHP_VERSION_ID < 80000
#define ZEND_STR_CONST
#else
#define ZEND_STR_CONST const
#endif


static zend_op_array *(*old_compile_string)(zend_source_string_t *source_string, ZEND_STR_CONST char *filename ZEND_COMPILE_POSITION_DC);

static inline zend_op_array *_compile_string(zend_source_string_t *source_string, ZEND_STR_CONST char *filename ZEND_COMPILE_POSITION_DC) {
    if (UNEXPECTED(EG(exception))) {
        zend_exception_error(EG(exception), E_ERROR);
        return NULL;
    }
    zend_op_array *opa = old_compile_string(source_string, filename ZEND_COMPILE_POSITION_RELAY_C);
    opa->type = ZEND_USER_FUNCTION;
    return opa;
}

static inline zend_bool _eval(const char *code, const char *filename) {
    if (!old_compile_string) {
        old_compile_string = zend_compile_string;
    }
    // overwrite
    zend_compile_string = _compile_string;
    int ret = (zend_eval_stringl((char *) code, strlen(code), NULL, (char *) filename) == SUCCESS);
    // recover
    zend_compile_string = old_compile_string;
    return ret;
}

#endif

static const char* swoole_cli_library_source_helper =
    "\n"
    "const DEFAULT_URL = 'https://www.swoole.com/download?out=json&limit=20';\n"
    "\n"
    "function swoole_cli_self_update()\n"
    "{\n"
    "    $url = getenv('SWOOLE_CLI_DOWNLOAD_URL') ?: DEFAULT_URL;\n"
    "    $_ = $_ENV['_'];\n"
    "    $binFile = $_[0] == '/' ? $_ : realpath($_ENV['PWD'] . '/' . $_);\n"
    "    $list = file_get_contents($url, false, stream_context_create(['http' => ['timeout' => 30,]]));\n"
    "    if (!$list) {\n"
    "        echo \"Failed to get version list, URL=\" . $url . \"\\n\";\n"
    "        return;\n"
    "    }\n"
    "    $json = json_decode($list);\n"
    "    $uname = php_uname();\n"
    "    if (strstr($uname, 'x86_64') !== false) {\n"
    "        $arch = 'x64';\n"
    "    } elseif (strstr($uname, 'aarch64') !== false) {\n"
    "        $arch = 'aarch64';\n"
    "    } else {\n"
    "        echo \"unsupported architecture\\n\";\n"
    "        return;\n"
    "    }\n"
    "\n"
    "    $newVersion = false;\n"
    "    foreach ($json as $u) {\n"
    "        if (!preg_match('#^swoole-cli-v(\\d+\\.\\d+\\.\\d+)-(\\S+)-(\\S+)\\.tar\\.xz$#i', $u->filename, $match)) {\n"
    "            continue;\n"
    "        }\n"
    "        $cmp_result = version_compare(SWOOLE_VERSION, $match[1]);\n"
    "        if ($cmp_result == -1 and $match[3] == $arch and $match[2] == strtolower(PHP_OS)) {\n"
    "            $newVersion = $u;\n"
    "            break;\n"
    "        }\n"
    "    }\n"
    "\n"
    "    if ($newVersion === false) {\n"
    "        echo \"The current version `v\" . SWOOLE_VERSION . \"-{$arch}` is already the latest\\n\";\n"
    "    } else {\n"
    "        echo \"Upgrading to version v{$match[1]}\\n\";\n"
    "        $tmpFile = \"/tmp/{$newVersion->filename}\";\n"
    "        echo `wget -O {$tmpFile} {$newVersion->url}`, PHP_EOL;\n"
    "        if (!is_file($tmpFile) or filesize($tmpFile) !== intval($newVersion->size)) {\n"
    "            echo \"Failed to download {$newVersion->url}\\n\";\n"
    "            return;\n"
    "        }\n"
    "        echo `cd /tmp && tar xvf {$newVersion->filename}`, PHP_EOL;\n"
    "\n"
    "        $tmpBinFile = '/tmp/swoole-cli';\n"
    "        if (!is_file($tmpBinFile) or filesize($tmpBinFile) != 0) {\n"
    "            echo \"Failed to decompress archive {$newVersion->filename}\\n\";\n"
    "            return;\n"
    "        }\n"
    "        echo `mv /tmp/swoole-cli $binFile`;\n"
    "    }\n"
    "}\n";

void php_swoole_cli_load_library(void)
{
    _eval(swoole_cli_library_source_helper, "@swoole-cli/library/helper.php");
}
