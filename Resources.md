# Introduction #

Items are arranged in a tree structure with groups at the top level, followed by (sub)groups and rooms underneath. Beds can only be defined under a room.

# Details #

![http://hostel-reservations.googlecode.com/svn/wiki/images/resources_sample.png](http://hostel-reservations.googlecode.com/svn/wiki/images/resources_sample.png)

Admin users will see a Resources tab on the left sidebar. Clicking on it will bring up this screen. Resources can be added to the right. Rooms should be grouped into distinct categories that will be used when allocating a booking. For example, a group name could be called "HostelWorld 10-bed dorms". Then this group can be selected when creating a new booking to automatically assign a guest to an available bed within this group.

Define high-level groups first, followed by rooms and finally beds. When defining room type (for dorm rooms), you can select "Male only", "Female only", "Mixed", or "None". A room defined as "Male only" will only allow booking for guests who specifically request a "Male only" room. Likewise for "Female only" and "Mixed". A dorm room with a room type of "None" will take on the attribute of a Male, Female or Mixed room depending on which guest(s) are currently assigned to it on a particular day. (A Male only room one day could just as easily be a Female only room the next).

Capacity will auto-create the selected number of beds after creating the room. Editing the bed names can be done afterwards.

The list of defined resources will be listed as a table in a familiar tree format. Once a resource is created, the name can be changed by clicking on the "Edit" icon under "Actions". Click "Save" to apply the changes immediately.

Clicking "Delete" will delete the selected group/room/bed and all elements underneath. Note that you will not be able to delete any resource that has an allocation (future or past) assigned to it.

At the moment, you are not able to moved a group/room to another group once it has been created. (This can be done directly on the db for now).

### Resource Properties ###

You are also able to define properties for a "shared room". Properties are another way to group rooms into specific categories to be used when allocating guests. Click on the notepad icon ![http://hostel-reservations.googlecode.com/svn/trunk/wordpress/wp-content/plugins/booking/img/notes_rd.png](http://hostel-reservations.googlecode.com/svn/trunk/wordpress/wp-content/plugins/booking/img/notes_rd.png) to view the Edit Properties screen.

![http://hostel-reservations.googlecode.com/svn/wiki/images/resource_properties_sample.png](http://hostel-reservations.googlecode.com/svn/wiki/images/resource_properties_sample.png)

For example, a room can be set to have a property called "ocean view". During allocation, you can select this as a required property when allocating the guests so only beds in these rooms will allocated if availability exists. See [AddBooking](AddBooking.md) for details on allocations.

The different property values can be defined under the Settings admin menu.