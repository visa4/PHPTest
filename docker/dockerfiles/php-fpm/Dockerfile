# webapu php-fpm
# @version 5.6.10
FROM centos:centos7

# prerequisites
RUN yum install -y gcc gcc-c++ kernel-devel libxml2-devel libxslt-devel openssl-devel libcurl-devel readline-devel bzip2-devel tar wget make autoconf libpng-devel

# locale
RUN localedef -c -i en_GB -f UTF-8 en_GB.UTF-8
ENV LANG en_GB.UTF-8
ENV LANGUAGE en_GB:en
ENV LC_ALL en_GB.UTF-8

WORKDIR /tmp

# icu4c # @see http://www.linuxfromscratch.org/blfs/view/svn/general/icu.html
RUN wget -nv -O - http://download.icu-project.org/files/icu4c/55.1/icu4c-55_1-src.tgz | tar zx
WORKDIR icu/source
RUN ./configure
RUN make
RUN make install

WORKDIR /tmp

# php
RUN wget -nv -O - http://uk1.php.net/distributions/php-5.6.10.tar.gz | tar zx
WORKDIR php-5.6.10
RUN rm configure
RUN ./buildconf --force
RUN ./configure --prefix=/usr/local/php --with-config-file-path=/usr/local/php/etc/ --disable-cgi --without-pear --without-sqlite3 --with-zlib --without-pdo-sqlite --with-curl=/usr/bin/curl --with-openssl --libdir=/usr/lib64 --with-libdir=lib64 --enable-opcache --enable-fileinfo --enable-mbstring --with-readline --enable-fpm --enable-zip --enable-intl --with-pdo-mysql --with-mysqli
RUN make
RUN make install
RUN make test
RUN useradd fpm
RUN ln -s /usr/local/php/bin/* /usr/local/bin

WORKDIR /tmp

# mongo driver
RUN wget -nv -O - http://pecl.php.net/get/mongo-1.6.9.tgz | tar zx
WORKDIR mongo-1.6.9/
RUN phpize
RUN ./configure
RUN make
RUN make install

WORKDIR /tmp

# libjpeg
RUN wget -nv -O - http://www.ijg.org/files/jpegsrc.v8c.tar.gz | tar zx
WORKDIR jpeg-8c/
RUN ./configure
RUN make
RUN make install

WORKDIR /tmp

# image magick
RUN mkdir -p imagemagick
WORKDIR imagemagick
RUN wget -nv -O - http://www.imagemagick.org/download/ImageMagick.tar.gz | tar zx --strip-components 1
RUN ./configure
RUN make
RUN make install
RUN ldconfig /usr/local/lib

WORKDIR /tmp

# imagick driver
RUN wget -nv -O - https://pecl.php.net/get/imagick-3.1.2.tgz | tar xz
WORKDIR imagick-3.1.2/
RUN phpize
RUN ./configure --with-imagick=/usr/local/bin/convert
RUN make
RUN make install

WORKDIR /tmp

# intl ext
# RUN wget -nv -O - https://pecl.php.net/get/intl-3.0.0.tgz | tar zx
# WORKDIR intl-3.0.0
# RUN phpize
# RUN LD_LIBRARY_PATH=/usr/local/lib ./configure
# RUN make
# RUN make install

WORKDIR /tmp

# xdebug
RUN wget -nv -O - http://xdebug.org/files/xdebug-2.3.1.tgz | tar zx
WORKDIR xdebug-2.3.1
RUN phpize
RUN ./configure
RUN make
RUN make install

WORKDIR /var/www

# xhprof
RUN wget -nv -O - http://pecl.php.net/get/xhprof-0.9.4.tgz | tar zx
RUN mv xhprof-0.9.4 xhprof
WORKDIR xhprof/extension
RUN phpize
RUN ./configure
RUN make
RUN make install

WORKDIR /var/www

VOLUME ["/usr/local/php/etc"]
ENTRYPOINT ["/usr/local/php/sbin/php-fpm"]
CMD ["-F"]
