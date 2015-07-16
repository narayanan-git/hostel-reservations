# Introduction #

The search page allows a user to fetch an existing booking.

# Details #

![http://hostel-reservations.googlecode.com/svn/wiki/images/bookings_sample.png](http://hostel-reservations.googlecode.com/svn/wiki/images/bookings_sample.png)

Booking Status: Only show those bookings with _any_ allocation for a particular booking matching this status (e.g. reserved, paid, cancelled). Default is ALL.

Date from/to: provide the from/to dates inclusive to search by. The dates here is used in conjunction with the "Match Dates By" field following.

Match Dates By:
  * Check-In Date (default): Show those bookings where the "check-in" date for a booking (the first date of a date-range for an allocation) falls within the to/from dates provided.
  * Check-Out Date: Show those bookings where the "check-out" date for a booking (the last date of a date-range for an allocation) falls within the to/from dates provided.
  * Reservation Date (Any): Show those bookings where _any_ allocation date (regardless of status) falls within the to/from dates provided.
  * Date Added: Show those bookings where the date the booking was created in the system falls within the to/from dates provided.

Search by Booking ID: enter the unique booking id to search for an exact booking

Search by Name: Show those bookings where either the first/last name for the booking or the guest name for an allocation matches. Search is _NOT_ case sensitive. (`*`) can be used as a wildcard for any number of characters.

Example 1: `sara*` will match "sara" or "sarah" or "sarandon".

Example 2: `rebe*a` will match "Rebecca" or "Rebeka".

Print: displays a printer-friendly table of the current results along with a print dialog (not yet implemented).

Export: exports the current results to a CSV/Excel file (not yet implemented).

## Search Results ##

ID: ID of the booking as well as the creation date and user who created the booking.

Tags: Shows the properties for the booking. This includes: the room(s) allocated, the active statuses and the manner in which the booking was made (e.g. telephone, HostelWorld, etc...)

Booking Details: Names/Guests and user-created comments.

Booking Dates: Dates across all allocations.

Actions: Clicking on the pencil icon will redirect to the [Edit Booking](AddBooking.md) screen. Clicking on the Exit icon will change all allocations ending today to "checked-out" (not yet implemented).