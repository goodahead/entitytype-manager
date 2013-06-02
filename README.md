Entity Type Manager
===================

This extension allows you to create and manage custom entity types from Magento admin panel.
You can define your own attributes for custom entity types, add entities and bind specific
entity type to Product attributes via source models.

Features
--------

* Create and manage custom entity types
* Add attributes for custom entity types
* Create/Manage entities
* Separate landing pages for entities on frontend
* Bind product attributes to entity type on creation
* Display link to entity landing page on product view page on frontend


Tasks (must have)
-----------------

###### Entity type manager
1. <del>Create grid for custom entity types with CRUD functionality</del>
2. <del>Create entity type Add/Edit form</del>
3. <del>Create corresponding models to work with custom entity types</del>

###### Attributes manager
1. <del>Create entity type attribute manager grid with CRUD functionality</del>
2. <del>Create attribute Add/Edit form with following fields:</del>
    * <del>Attribute Code</del>
    * <del>Attribute Name</del>
    * <del>Attribute Scope (Global/Website/Store View)</del>
    * <del>Attribute Type (Same as for Catalog/Product attributes)</del>
3. <del>Create corresponding models to work with custom attributes</del>

###### Entities manager
1. Create entity grid with CRUD functionality
2. Create entity Add/Edit form
3. Add WYSIWYG editor support for attributes of type text
4. Create corresponding models to work with entity
5. Add multi store view support for entity type data to allow translations

###### Custom admin menu
1. <del>For Attribute manager and Entity manager menu items should be based on existing custom entity types</del>
2. <del>Hide Attribute manager and Entity manager menu when no custom entity types defined</del>

###### Catalog Product bindings
1. <del>When creating attribute with type Select or Multi-select add an option to bind this attribute to existing entity type.
This will set up source model and frontend renderer for this attribute.<del>
2. On frontend have custom renderer for attributes that defined as "Visible on product view page on frontend" to show
link to entity type landing page.

###### Entity type frontend landing pages
1. Landing page HTML should be rendered using email template approach when customer can define variable placeholders.

Tasks (nice to have)
--------------------

1. Media gallery support for attributes
2. Add mass actions to grid for delete operations
3. UrlRewrites for frontend SEO url for entities (this include URL model)