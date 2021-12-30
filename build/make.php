<?php
/**
 * @var $this Preprocessor
 */
?>
SRC=/home/htf/soft/php-8.1.1
ROOT=$(pwd)
export CC=clang
export CXX=clang++
export LD=ld.lld
OPTIONS="--disable-all \
<?php foreach ($this->extensionList as $item) : ?>
<?=$item->options?> \
<?php endforeach; ?>
"

<?php foreach ($this->libraryList as $item) : ?>
make_<?=$item->name?>() {
    cd /work/pool/lib
    echo "build <?=$item->name?>"
    mkdir -p /work/pool/lib/<?=$item->name?> && \
    tar --strip-components=1 -C /work/pool/lib/<?=$item->name?> -xf /work/pool/lib/<?=$item->file?>  && \
    cd <?=$item->name?> && \
    echo  "<?=$item->configure?>"
    <?php if (!empty($item->configure)): ?>
    <?=$item->configure?> && \
    <?php endif; ?>
    make -j <?=$this->maxJob?>  <?=$item->makeOptions?> && \
    make install
}
<?php echo str_repeat(PHP_EOL, 1);?>
<?php endforeach; ?>

make_all_library() {
<?php foreach ($this->libraryList as $item) : ?>
    make_<?=$item->name?> && echo "[SUCCESS] make <?=$item->name?>"
<?php endforeach; ?>
}

if [ "$1" = "docker-build" ] ;then
  sudo docker build -t phpswoole/swoole_cli_os:latest .
elif [ "$1" = "docker-bash" ] ;then
  sudo docker run -it -v $ROOT:/work -v /home/htf/workspace/swoole:/work/ext/swoole phpswoole/swoole_cli_os /bin/bash
elif [ "$1" = "config" ] ;then
   echo $OPTIONS
  ./configure $OPTIONS
elif [ "$1" = "all-library" ] ;then
    make_all_library
<?php foreach ($this->libraryList as $item) : ?>
elif [ "$1" = "<?=$item->name?>" ] ;then
    make_<?=$item->name?> && echo "[SUCCESS] make <?=$item->name?>"
<?php endforeach; ?>
elif [ "$1" = "static-config" ] ;then
   rm ./configure
   ./buildconf --force
   mv main/php_config.h.in /tmp/cnt
   echo -ne '#ifndef __PHP_CONFIG_H\n#define __PHP_CONFIG_H\n' > main/php_config.h.in
   cat /tmp/cnt >> main/php_config.h.in
   echo -ne '\n#endif\n' >> main/php_config.h.in
   echo $OPTIONS
   export PKG_CONFIG_PATH=/usr/openssl/lib/pkgconfig:/usr/curl/lib/pkgconfig:$PKG_CONFIG_PATH
  ./configure $OPTIONS
elif [ "$1" = "static-build" ] ;then
make EXTRA_CFLAGS='-fno-ident -Xcompiler -march=nehalem -Xcompiler -mtune=haswell -Os' \
EXTRA_LDFLAGS_PROGRAM='-all-static -fno-ident <?php foreach ($this->libraryList as $item) {
    if (!empty($item->ldflags)) {
        echo $item->ldflags;
    }
} ?>'  -j <?=$this->maxJob?> && echo ""
elif [ "$1" = "diff-configure" ] ;then
  meld $SRC/configure.ac ./configure.ac
elif [ "$1" = "sync" ] ;then
  # ZendVM
  cp -r $SRC/Zend ./
  # Extension
  cp -r $SRC/ext/bcmath/ ./ext
  cp -r $SRC/ext/bz2/ ./ext
  cp -r $SRC/ext/calendar/ ./ext
  cp -r $SRC/ext/ctype/ ./ext
  cp -r $SRC/ext/curl/ ./ext
  cp -r $SRC/ext/date/ ./ext
  cp -r $SRC/ext/dom/ ./ext
  cp -r $SRC/ext/exif/ ./ext
  cp -r $SRC/ext/fileinfo/ ./ext
  cp -r $SRC/ext/filter/ ./ext
  cp -r $SRC/ext/gd/ ./ext
  cp -r $SRC/ext/gettext/ ./ext
  cp -r $SRC/ext/gmp/ ./ext
  cp -r $SRC/ext/hash/ ./ext
  cp -r $SRC/ext/iconv/ ./ext
  cp -r $SRC/ext/intl/ ./ext
  cp -r $SRC/ext/json/ ./ext
  cp -r $SRC/ext/libxml/ ./ext
  cp -r $SRC/ext/mbstring/ ./ext
  cp -r $SRC/ext/mysqli/ ./ext
  cp -r $SRC/ext/mysqlnd/ ./ext
  cp -r $SRC/ext/opcache/ ./ext
  cp -r $SRC/ext/openssl/ ./ext
  cp -r $SRC/ext/pcntl/ ./ext
  cp -r $SRC/ext/pcre/ ./ext
  cp -r $SRC/ext/pdo/ ./ext
  cp -r $SRC/ext/pdo_mysql/ ./ext
  cp -r $SRC/ext/pdo_sqlite/ ./ext
  cp -r $SRC/ext/phar/ ./ext
  cp -r $SRC/ext/posix/ ./ext
  cp -r $SRC/ext/readline/ ./ext
  cp -r $SRC/ext/reflection/ ./ext
  cp -r $SRC/ext/session/ ./ext
  cp -r $SRC/ext/simplexml/ ./ext
  cp -r $SRC/ext/soap/ ./ext
  cp -r $SRC/ext/sockets/ ./ext
  cp -r $SRC/ext/sodium/ ./ext
  cp -r $SRC/ext/spl/ ./ext
  cp -r $SRC/ext/sqlite3/ ./ext
  cp -r $SRC/ext/standard/ ./ext
  cp -r $SRC/ext/sysvshm/ ./ext
  cp -r $SRC/ext/tokenizer/ ./ext
  cp -r $SRC/ext/xml/ ./ext
  cp -r $SRC/ext/xmlreader/ ./ext
  cp -r $SRC/ext/xmlwriter/ ./ext
  cp -r $SRC/ext/xsl/ ./ext
  cp -r $SRC/ext/zip/ ./ext
  cp -r $SRC/ext/zlib/ ./ext
  # main
  cp -r $SRC/main ./main
  cp -r sapi/cli sapi/cli
  cp -r ./TSRM/TSRM.h main/TSRM.h
  exit 0
fi

