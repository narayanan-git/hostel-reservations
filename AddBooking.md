# Introduction #

Add a new booking/allocation or edit an existing booking.


# Details #

![http://hostel-reservations.googlecode.com/svn/wiki/images/add_booking_sample.png](http://hostel-reservations.googlecode.com/svn/wiki/images/add_booking_sample.png)

Admin users (reception staff) clicks "Add Booking" under the left sidebar to enter a new booking. To edit an existing booking, click on the link/icon of the booking/allocation to edit in the [Allocation](Allocations.md) or [Booking](Bookings.md) view.

First Name, Last Name: self-explanatory. this is the name the booking will be under. Only a first name is required.

Booked by: How the booking was originally made. e.g. HostelWorld, HostelBookers, Telephone, Walk-in, etc..

Audit Log/Comments: User comments can be entered here. e.g. Amount payable on arrival can be manually entered here. Future functionality may include a separate field to sum payment amounts for each booking/day. This also holds a log of all changes made to the booking since it was created including changes to the allocations, change in status (from reserved to paid for example) along with the date/time and user who made the change.

## Allocations ##

This is where we define all the guest(s) for this booking and their room/bed allocations. Select the dates for the booking using the calendar widget.

Room/Bed: Select an individual bed (for 1 guest), or select a room/group to auto-allocate the number of guest among the available beds.

Requested Room Type: Select from Mixed, Male or Female rooms. Rooms that have defined room types will be assigned first followed by rooms without specific gender assignments (see Resources).

Assign To: Only assign to rooms with the selected properties ticked (see Resource Properties).

Clicking "Add" will allocate the guest(s) to the selected group/room(s) displayed in a table placed below under the status of "Reserved". Clicking on the date cell in the table will toggle the state from Reserved to Paid/Checked-in, Free, etc..

"Save" will permanently commit these changes.