#!/bin/sh

# reload all sample transactional data
~/public_html/castlerock/wp-content/plugins/booking/sql/loadsql.sh ~/public_html/castlerock/wp-content/plugins/booking/sql/wp_bookingresources_cr.sql
~/public_html/castlerock/wp-content/plugins/booking/sql/loadsql.sh ~/public_html/castlerock/wp-content/plugins/booking/sql/wp_booking.sql
~/public_html/castlerock/wp-content/plugins/booking/sql/loadsql.sh ~/public_html/castlerock/wp-content/plugins/booking/sql/wp_allocation.sql
~/public_html/castlerock/wp-content/plugins/booking/sql/loadsql.sh ~/public_html/castlerock/wp-content/plugins/booking/sql/wp_bookingcomment.sql
~/public_html/castlerock/wp-content/plugins/booking/sql/loadsql.sh ~/public_html/castlerock/wp-content/plugins/booking/sql/wp_bookingdates.sql
