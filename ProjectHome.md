This [wordpress](http://wordpress.org) plugin enables hostel staff to create/edit bookings and allocate guests to private rooms or individual dorm beds. Rooms can be arranged into groups and allocations performed based on a selected criteria. At the moment, no payment information is saved although a generic comment field can be used to enter payment information. Payment field(s) may be added on later as required.

### Current supported functionality: ###

  * Ability to setup and arrange an unlimited number of rooms/beds. Able to handle dorm beds as well as private rooms.
  * Ability to add a new booking and allocate the booking at the same time. A new booking will not be saved unless it can be allocated to an available room/bed. At no point can 2 separate bookings share the same dorm bed (or private room) on the same day.
  * Rooms can be designated as Male only, Female only, Mixed or Open (open rooms may change from male/female/mixed depending on the day and the requested room types of the guests assigned to the room)
  * Bookings and allocations will be able to be modified at any time including room reassignment.
  * Cancellations are allowed but will be retained in the system and remain searchable for auditing purposes. A cancellation will free up the previously assigned rooms/beds for future bookings.
  * Bookings are searchable by id, name, status, and/or date range.
  * Allocations and their statuses (e.g. Paid, Reserved, etc..) can be viewed across all rooms for a selected date-range.
  * Daily summary shows the number of dorm beds/private rooms available for a given day. Ability to drill down into individual rooms as necessary.
  * Housekeeping summary shows the rooms/beds that require sheets to be changed for a particular day.

### Nice to have but not yet supported functionality: ###

  * HostelWorld/HostelBookers/CRNet synchronisation
  * Payment rules (e.g. weekly rates, pricing changes on weekends/holiday periods, cash-out reports)
  * Database redundancy/backup - automatic failover? recovery procedures?