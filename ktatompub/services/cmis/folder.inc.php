<?php

/**
 * Folder access/management functions for CMIS AtomPub
 * Output returned as an AtomPub feed
 */

include 'services/cmis/ObjectFeed.inc.php';

class CMISFolderFeed extends CMISObjectFeed {

    /**
     * Retrieves children/descendants of the specified folder
     * TODO this currently only works in children mode, add descendants
     *
     * @param string $repositoryId
     * @param string $folderId folder id for which children/descendants are requested
     * @param string $feedType children or descendants
     * @return string CMIS AtomPub feed
     */
    static public function getFolderChildrenFeed($NavigationService, $repositoryId, $folderId, $folderName, $feedType)
    {
        if ($feedType == 'children')
        {
            $entries = $NavigationService->getChildren($repositoryId, $folderId, false, false);
        }
        else if ($feedType == 'descendants')
        {
            $entries = $NavigationService->getDescendants($repositoryId, $folderId, $includeAllowableActions, $includeRelationships);
        }
        else
        {
            // error, we shouldn't be here, if we are then the wrong function was called
        }

        $feed = new KTCMISAPPFeed(KT_APP_BASE_URI, $folderName . ' ' . ucwords($feedType), null, null, null,
                                  'urn:uuid:' . $folderName . '-' . $feedType);

        foreach($entries as $cmisEntry)
        {
            CMISFolderFeed::createEntry($feed, $cmisEntry, $folderName);
        }

        // <cmis:hasMoreItems>false</cmis:hasMoreItems>

        $output = $feed->getAPPdoc();
        $outputs = '<?xml version="1.0" encoding="utf-8"?>
        <feed xmlns="http://www.w3.org/2005/Atom" xmlns:cmis="http://www.cmis.org/2008/05">
         <id>urn:uuid:28537649-8af2-4c74-aa92-5d8bbecac9ce-children</id>
         <link rel="self" href="http://10.33.4.34/ktatompub/?/cmis/folder/F1/children"/>
         <title>Root Folder Children</title>
         <entry>
          <id>urn:uuid:86224486-b7ae-4074-a793-82cd259b0026-folder</id>
          <link rel="cmis-children" href="http://10.33.4.34/ktatompub/?cmis/folder/F2/children"/>

          <link rel="cmis-descendants" href="http://10.33.4.34/ktatompub/?cmis/folder/F2/descendants"/>
          <link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/folder"/>
          <link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
          <summary>DroppedDocuments</summary>
          <title>DroppedDocuments</title>
          <cmis:object>
           <cmis:properties>
            <cmis:propertyId cmis:name="ObjectId">

             <cmis:value>F2</cmis:value>
            </cmis:propertyId>
            <cmis:propertyString cmis:name="ObjectTypeId">
             <cmis:value>Folder</cmis:value>
            </cmis:propertyString>
            <cmis:propertyString cmis:name="Name">
             <cmis:value>DroppedDocuments</cmis:value>

            </cmis:propertyString>
           </cmis:properties>
          </cmis:object>
         </entry>
         <entry>
          <id>urn:uuid:86224486-b7ae-4074-a793-82cd259b0026-folder</id>
          <link rel="cmis-children" href="http://10.33.4.34/ktatompub/?cmis/folder/F4/children"/>
          <link rel="cmis-descendants" href="http://10.33.4.34/ktatompub/?cmis/folder/F4/descendants"/>

          <link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/folder"/>
          <link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
          <summary>Test KT Folder</summary>
          <title>Test KT Folder</title>
          <cmis:object>
           <cmis:properties>
            <cmis:propertyId cmis:name="ObjectId">
             <cmis:value>F4</cmis:value>

            </cmis:propertyId>
            <cmis:propertyString cmis:name="ObjectTypeId">
             <cmis:value>Folder</cmis:value>
            </cmis:propertyString>
            <cmis:propertyString cmis:name="Name">
             <cmis:value>Test KT Folder</cmis:value>
            </cmis:propertyString>
           </cmis:properties>

          </cmis:object>
         </entry>
        <entry>
         <author><name>admin</name></author>
        <content type="application/pdf" src="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><id>urn:uuid:2df9d676-f173-47bb-8ec1-41fa1186b66d</id>
        <link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d"/>

        <link rel="enclosure" type="application/pdf" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d"/>
        <link rel="edit-media" type="application/pdf" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/permissions"/>
        <link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/associations"/>
        <link rel="cmis-parents" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/parents"/>
        <link rel="cmis-allversions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/versions"/>
        <link rel="cmis-stream" type="application/pdf" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/document"/>
        <link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
        <published>2009-06-23T09:40:47.889+02:00</published>
        <summary></summary>
        <title>h4555-cmis-so.pdf</title>
        <updated>2009-06-23T09:40:58.524+02:00</updated>

        <cmis:object>
        <cmis:properties>
        <cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d</cmis:value></cmis:propertyId>
        <cmis:propertyString cmis:name="BaseType"><cmis:value>document</cmis:value></cmis:propertyString>
        <cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>document</cmis:value></cmis:propertyString>
        <cmis:propertyString cmis:name="CreatedBy"><cmis:value>admin</cmis:value></cmis:propertyString>

        <cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-23T09:40:47.889+02:00</cmis:value></cmis:propertyDateTime>
        <cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>admin</cmis:value></cmis:propertyString>
        <cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-23T09:40:58.524+02:00</cmis:value></cmis:propertyDateTime>
        <cmis:propertyString cmis:name="Name"><cmis:value>h4555-cmis-so.pdf</cmis:value></cmis:propertyString>
        <cmis:propertyBoolean cmis:name="IsImmutable"><cmis:value>false</cmis:value></cmis:propertyBoolean>

        <cmis:propertyBoolean cmis:name="IsLatestVersion"><cmis:value>true</cmis:value></cmis:propertyBoolean>
        <cmis:propertyBoolean cmis:name="IsMajorVersion"><cmis:value>false</cmis:value></cmis:propertyBoolean>
        <cmis:propertyBoolean cmis:name="IsLatestMajorVersion"><cmis:value>false</cmis:value></cmis:propertyBoolean>
        <cmis:propertyString cmis:name="VersionLabel"/>
        <cmis:propertyId cmis:name="VersionSeriesId"><cmis:value>workspace://SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d</cmis:value></cmis:propertyId>

        <cmis:propertyBoolean cmis:name="IsVersionSeriesCheckedOut"><cmis:value>false</cmis:value></cmis:propertyBoolean>
        <cmis:propertyString cmis:name="VersionSeriesCheckedOutBy"/>
        <cmis:propertyId cmis:name="VersionSeriesCheckedOutId"/>
        <cmis:propertyString cmis:name="CheckinComment"/>
        <cmis:propertyInteger cmis:name="ContentStreamLength"><cmis:value>343084</cmis:value></cmis:propertyInteger>
        <cmis:propertyString cmis:name="ContentStreamMimeType"><cmis:value>application/pdf</cmis:value></cmis:propertyString>
        <cmis:propertyString cmis:name="ContentStreamFilename"><cmis:value>h4555-cmis-so.pdf</cmis:value></cmis:propertyString>

        <cmis:propertyString cmis:name="ContentStreamURI"><cmis:value>http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf</cmis:value></cmis:propertyString>
        </cmis:properties>
        </cmis:object>


         </entry>
        </feed>';

        $outputs = '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:app="http://www.w3.org/2007/app" xmlns:cmis="http://www.cmis.org/2008/05" xmlns:alf="http://www.alfresco.org" xmlns:opensearch="http://a9.com/-/spec/opensearch/1.1/">
<author><name>System</name></author>
<generator version="3.0.0 (Stable 1526)">Alfresco (Labs)</generator>
<icon>http://10.33.4.34:8080/alfresco/images/logo/AlfrescoLogo16.ico</icon>
<id>urn:uuid:28537649-8af2-4c74-aa92-5d8bbecac9ce-children</id>
<link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/path/workspace/SpacesStore/Company%20Home/children"/>
<link rel="cmis-source" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="first" href="http://10.33.4.34:8080/alfresco/service/api/path/workspace/SpacesStore/Company%20Home/children?pageNo=1&amp;pageSize=0&amp;guest=&amp;format=atomfeed" type="application/atom+xml;type=feed"/>

<link rel="last" href="http://10.33.4.34:8080/alfresco/service/api/path/workspace/SpacesStore/Company%20Home/children?pageNo=1&amp;pageSize=0&amp;guest=&amp;format=atomfeed" type="application/atom+xml;type=feed"/>
<title>Company Home Children</title>
<updated>2009-06-18T10:20:29.937+02:00</updated>
<entry>
<author><name>System</name></author>
<content>e98319fa-76e4-478f-8ce8-a3a0fd683e2c</content>
<id>urn:uuid:e98319fa-76e4-478f-8ce8-a3a0fd683e2c</id>

<link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c"/>
<link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c"/>
<link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c/permissions"/>
<link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c/associations"/>
<link rel="cmis-parent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="cmis-folderparent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c/parent"/>
<link rel="cmis-children" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c/children"/>
<link rel="cmis-descendants" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c/descendants"/>
<link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/F/st_sites"/>
<link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
<published>2009-06-18T10:20:37.788+02:00</published>
<summary>Site Collaboration Spaces</summary>
<title>Sites</title>
<updated>2009-06-18T10:20:37.874+02:00</updated>

<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/e98319fa-76e4-478f-8ce8-a3a0fd683e2c</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>F/st_sites</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>System</cmis:value></cmis:propertyString>

<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-18T10:20:37.788+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-18T10:20:37.874+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>Sites</cmis:value></cmis:propertyString>
<cmis:propertyId cmis:name="ParentId"><cmis:value>workspace://SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce</cmis:value></cmis:propertyId>

</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-18T10:20:37.874+02:00</app:edited>
<alf:icon>http://10.33.4.34:8080/alfresco/images/icons/space-icon-default-16.gif</alf:icon>
</entry>
<entry>
<author><name>System</name></author>
<content>8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10</content>
<id>urn:uuid:8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10</id>

<link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10"/>
<link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10"/>
<link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10/permissions"/>
<link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10/associations"/>
<link rel="cmis-parent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="cmis-folderparent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10/parent"/>
<link rel="cmis-children" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10/children"/>
<link rel="cmis-descendants" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10/descendants"/>
<link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/folder"/>
<link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
<published>2009-06-18T10:20:29.939+02:00</published>
<summary>User managed definitions</summary>
<title>Data Dictionary</title>
<updated>2009-06-18T10:20:30.004+02:00</updated>

<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/8c80a0f7-74b4-4bd8-bb76-a2464e4b2d10</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>System</cmis:value></cmis:propertyString>

<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-18T10:20:29.939+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-18T10:20:30.004+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>Data Dictionary</cmis:value></cmis:propertyString>
<cmis:propertyId cmis:name="ParentId"><cmis:value>workspace://SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce</cmis:value></cmis:propertyId>

</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-18T10:20:30.004+02:00</app:edited>
<alf:icon>http://10.33.4.34:8080/alfresco/images/icons/space-icon-default-16.gif</alf:icon>
</entry>
<entry>
<author><name>System</name></author>
<content>ba2524ef-7f3d-4ed4-84a0-8d99b6524737</content>
<id>urn:uuid:ba2524ef-7f3d-4ed4-84a0-8d99b6524737</id>

<link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737"/>
<link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737"/>
<link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737/permissions"/>
<link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737/associations"/>
<link rel="cmis-parent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="cmis-folderparent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737/parent"/>
<link rel="cmis-children" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737/children"/>
<link rel="cmis-descendants" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737/descendants"/>
<link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/folder"/>
<link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
<published>2009-06-18T10:20:30.312+02:00</published>
<summary>The guest root space</summary>
<title>Guest Home</title>

<updated>2009-06-18T10:20:30.400+02:00</updated>
<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/ba2524ef-7f3d-4ed4-84a0-8d99b6524737</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>System</cmis:value></cmis:propertyString>

<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-18T10:20:30.312+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-18T10:20:30.400+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>Guest Home</cmis:value></cmis:propertyString>
<cmis:propertyId cmis:name="ParentId"><cmis:value>workspace://SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce</cmis:value></cmis:propertyId>

</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-18T10:20:30.400+02:00</app:edited>
<alf:icon>http://10.33.4.34:8080/alfresco/images/icons/space-icon-default-16.gif</alf:icon>
</entry>
<entry>
<author><name>System</name></author>
<content>86224486-b7ae-4074-a793-82cd259b0026</content>
<id>urn:uuid:86224486-b7ae-4074-a793-82cd259b0026</id>

<link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/86224486-b7ae-4074-a793-82cd259b0026"/>
<link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/86224486-b7ae-4074-a793-82cd259b0026"/>
<link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/86224486-b7ae-4074-a793-82cd259b0026/permissions"/>
<link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/86224486-b7ae-4074-a793-82cd259b0026/associations"/>
<link rel="cmis-parent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="cmis-folderparent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/86224486-b7ae-4074-a793-82cd259b0026/parent"/>
<link rel="cmis-children" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/86224486-b7ae-4074-a793-82cd259b0026/children"/>
<link rel="cmis-descendants" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/86224486-b7ae-4074-a793-82cd259b0026/descendants"/>
<link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/folder"/>
<link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
<published>2009-06-18T10:20:30.402+02:00</published>
<summary>User Homes</summary>
<title>User Homes</title>
<updated>2009-06-18T10:20:30.428+02:00</updated>

<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/86224486-b7ae-4074-a793-82cd259b0026</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>System</cmis:value></cmis:propertyString>

<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-18T10:20:30.402+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-18T10:20:30.428+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>User Homes</cmis:value></cmis:propertyString>
<cmis:propertyId cmis:name="ParentId"><cmis:value>workspace://SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce</cmis:value></cmis:propertyId>

</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-18T10:20:30.428+02:00</app:edited>
<alf:icon>http://10.33.4.34:8080/alfresco/images/icons/space-icon-default-16.gif</alf:icon>
</entry>
<entry>
<author><name>System</name></author>
<content>0df9087f-e334-4890-a467-b60e3d6be92c</content>
<id>urn:uuid:0df9087f-e334-4890-a467-b60e3d6be92c</id>

<link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c"/>
<link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c"/>
<link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c/permissions"/>
<link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c/associations"/>
<link rel="cmis-parent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="cmis-folderparent" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c/parent"/>
<link rel="cmis-children" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c/children"/>
<link rel="cmis-descendants" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c/descendants"/>
<link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/folder"/>
<link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
<published>2009-06-18T10:20:45.115+02:00</published>
<summary>Web Content Management Spaces</summary>
<title>Web Projects</title>
<updated>2009-06-18T10:20:45.137+02:00</updated>

<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/0df9087f-e334-4890-a467-b60e3d6be92c</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>System</cmis:value></cmis:propertyString>

<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-18T10:20:45.115+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-18T10:20:45.137+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>Web Projects</cmis:value></cmis:propertyString>
<cmis:propertyId cmis:name="ParentId"><cmis:value>workspace://SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce</cmis:value></cmis:propertyId>

</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-18T10:20:45.137+02:00</app:edited>
<alf:icon>http://10.33.4.34:8080/alfresco/images/icons/space-icon-default-16.gif</alf:icon>
</entry>
<entry>
<author><name>admin</name></author>
<content type="application/pdf" src="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><id>urn:uuid:2df9d676-f173-47bb-8ec1-41fa1186b66d</id>
<link rel="self" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d"/>

<link rel="enclosure" type="application/pdf" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="edit" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d"/>
<link rel="edit-media" type="application/pdf" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="cmis-allowableactions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/permissions"/>
<link rel="cmis-relationships" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/associations"/>
<link rel="cmis-parents" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/parents"/>
<link rel="cmis-allversions" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/versions"/>
<link rel="cmis-stream" type="application/pdf" href="http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf"/><link rel="cmis-type" href="http://10.33.4.34:8080/alfresco/service/api/type/document"/>
<link rel="cmis-repository" href="http://10.33.4.34:8080/alfresco/service/api/repository"/>
<published>2009-06-23T09:40:47.889+02:00</published>
<summary></summary>
<title>h4555-cmis-so.pdf</title>
<updated>2009-06-23T09:40:58.524+02:00</updated>

<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>document</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>document</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>admin</cmis:value></cmis:propertyString>

<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-23T09:40:47.889+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>admin</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-23T09:40:58.524+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>h4555-cmis-so.pdf</cmis:value></cmis:propertyString>
<cmis:propertyBoolean cmis:name="IsImmutable"><cmis:value>false</cmis:value></cmis:propertyBoolean>

<cmis:propertyBoolean cmis:name="IsLatestVersion"><cmis:value>true</cmis:value></cmis:propertyBoolean>
<cmis:propertyBoolean cmis:name="IsMajorVersion"><cmis:value>false</cmis:value></cmis:propertyBoolean>
<cmis:propertyBoolean cmis:name="IsLatestMajorVersion"><cmis:value>false</cmis:value></cmis:propertyBoolean>
<cmis:propertyString cmis:name="VersionLabel"/>
<cmis:propertyId cmis:name="VersionSeriesId"><cmis:value>workspace://SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d</cmis:value></cmis:propertyId>

<cmis:propertyBoolean cmis:name="IsVersionSeriesCheckedOut"><cmis:value>false</cmis:value></cmis:propertyBoolean>
<cmis:propertyString cmis:name="VersionSeriesCheckedOutBy"/>
<cmis:propertyId cmis:name="VersionSeriesCheckedOutId"/>
<cmis:propertyString cmis:name="CheckinComment"/>
<cmis:propertyInteger cmis:name="ContentStreamLength"><cmis:value>343084</cmis:value></cmis:propertyInteger>
<cmis:propertyString cmis:name="ContentStreamMimeType"><cmis:value>application/pdf</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ContentStreamFilename"><cmis:value>h4555-cmis-so.pdf</cmis:value></cmis:propertyString>

<cmis:propertyString cmis:name="ContentStreamURI"><cmis:value>http://10.33.4.34:8080/alfresco/service/api/node/workspace/SpacesStore/2df9d676-f173-47bb-8ec1-41fa1186b66d/content.h4555-cmis-so.pdf</cmis:value></cmis:propertyString>
</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-23T09:40:58.524+02:00</app:edited>
<alf:icon>http://10.33.4.34:8080/alfresco/images/filetypes/pdf.gif</alf:icon>
</entry>
<cmis:hasMoreItems>false</cmis:hasMoreItems>
<opensearch:totalResults>6</opensearch:totalResults>
<opensearch:startIndex>0</opensearch:startIndex>

<opensearch:itemsPerPage>0</opensearch:itemsPerPage>
</feed>';

        return $output;
    }

    /**
     * Retrieves data about a specific folder
     *
     * @param object $ObjectService The CMIS service
     * @param string $repositoryId
     * @param string $folderId
     * @return string CMIS AtomPub feed
     */
    static public function getFolderFeed($ObjectService, $repositoryId, $folderId)
    {
        $cmisEntry = $ObjectService->getProperties($repositoryId, $folderId, false, false);

        $feed = new KTCMISAPPFeed(KT_APP_BASE_URI, $cmisEntry['properties']['ObjectTypeId']['value'], null, null, null,
                                  'urn:uuid:' . $cmisEntry['properties']['ObjectId']['value']);

        CMISFolderFeed::createEntry($feed, $cmisEntry, $cmisEntry['properties']['ParentId']['value']);

        // <cmis:hasMoreItems>false</cmis:hasMoreItems>

        $output = $feed->getAPPdoc();
    $outputs = '<?xml version="1.0" encoding="UTF-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xmlns:cmis="http://www.cmis.org/2008/05">
<entry>
<author><name>System</name></author>
<content>28537649-8af2-4c74-aa92-5d8bbecac9ce</content>
<id>urn:uuid:28537649-8af2-4c74-aa92-5d8bbecac9ce</id>
<link rel="self" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="edit" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce"/>
<link rel="cmis-allowableactions" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce/permissions"/>
<link rel="cmis-relationships" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce/associations"/>
<link rel="cmis-children" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce/children"/>
<link rel="cmis-descendants" href="http://127.0.0.1:8080/alfresco/service/api/node/workspace/SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce/descendants"/>
<link rel="cmis-type" href="http://127.0.0.1:8080/alfresco/service/api/type/folder"/>
<link rel="cmis-repository" href="http://127.0.0.1:8080/alfresco/service/api/repository"/>
<published>2009-06-18T10:20:29.871+02:00</published>
<summary>The company root space</summary>
<title>Company Home</title>
<updated>2009-06-18T10:20:29.937+02:00</updated>
<cmis:object>
<cmis:properties>
<cmis:propertyId cmis:name="ObjectId"><cmis:value>workspace://SpacesStore/28537649-8af2-4c74-aa92-5d8bbecac9ce</cmis:value></cmis:propertyId>
<cmis:propertyString cmis:name="BaseType"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="ObjectTypeId"><cmis:value>folder</cmis:value></cmis:propertyString>
<cmis:propertyString cmis:name="CreatedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="CreationDate"><cmis:value>2009-06-18T10:20:29.871+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="LastModifiedBy"><cmis:value>System</cmis:value></cmis:propertyString>
<cmis:propertyDateTime cmis:name="LastModificationDate"><cmis:value>2009-06-18T10:20:29.937+02:00</cmis:value></cmis:propertyDateTime>
<cmis:propertyString cmis:name="Name"><cmis:value>Company Home</cmis:value></cmis:propertyString>
<cmis:propertyId cmis:name="ParentId"/>
</cmis:properties>
</cmis:object>
<cmis:terminator/>
<app:edited>2009-06-18T10:20:29.937+02:00</app:edited>
<alf:icon>http://127.0.0.1:8080/alfresco/images/icons/space-icon-default-16.gif</alf:icon>
</entry>
</feed>';

        return $output;
    }

    static public function getFolderData($query, &$locationName, &$tree)
    {
        $folderId = null;

        // TODO proper login credentials, or rather use the existing session available from the underlying CMIS code
        $ktapi = new KTAPI();
        $ktapi->start_session('admin', 'admin');

        $numQ = count($query);

        if($query[$numQ-1] == 'children' || $query[$numQ-1] == 'descendants') {
            $offset = 1;
            $tree = $query[$numQ-1];
        }
        
        $folderName = urldecode($query[$numQ-($offset+1)]);

        $locationName = $folderName;

        if ($numQ <= 5)
        {
            $parentId = 1;
        }
        else
        {
            $count = 2;
            $lastParent = 0;

            while(++$count <= ($numQ - 3))
            {
                if ($lastParent == 0)
                {
                    $idUp = 1;
                }
                else
                {
                    $idUp = $lastParent;
                }

                $folderName = urldecode($query[$count]);
                $folder = $ktapi->get_folder_by_name($folderName, $idUp);

                if (PEAR::isError($folder)) break;

                $currentId = $folder->get_folderid();
                $lastParent = $currentId;
            }

            $parentId = $lastParent;
        }

        $folder = $ktapi->get_folder_by_name($locationName, $parentId);
        $folderId = CMISUtil::encodeObjectId('Folder', $folder->get_folderid());

        return $folderId;
    }
}

include 'services/cmis/RepositoryService.inc.php';
include 'services/cmis/NavigationService.inc.php';
include 'services/cmis/ObjectService.inc.php';

$RepositoryService = new RepositoryService();
$repositories = $RepositoryService->getRepositories();
$repositoryId = $repositories[0]['repositoryId'];

$folderId = CMISFolderFeed::getFolderData($query, $folderName, $tree);

if (isset($tree) && (($tree == 'children') || ($tree == 'descendants')))
{
    $NavigationService = new NavigationService();
    $NavigationService->startSession($username, $password);
    
    $output = CMISFolderFeed::getFolderChildrenFeed($NavigationService, $repositoryId, $folderId, $folderName, $tree);
}
else
{
    $ObjectService = new ObjectService();
    $ObjectService->startSession($username, $password);

    $output = CMISFolderFeed::getFolderFeed($ObjectService, $repositoryId, $folderId);
}

?>
