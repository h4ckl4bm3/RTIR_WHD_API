# RTIR_WHD_API
Installs onto an Apache server that also has Request Tracker for Incident Response running on it. I'm sure this could just as easily be written in perl as an extension to RT, but this was built separtely at first as a proof-of-concept and grew from there.

===Installation
Assuming that your RT installation is in /opt/rt4/...
```
cd /opt/
git clone https://github.com/sbsroc/RTIR_WHD_API.git
```
Edit RTIR_WHD_API/helpdesk/PSAIntegrator.php with an RT username and pssword, and create a "drag your hands accross the keyboard" type of API key to type into N-Central. Or go to https://www.grc.com/passwords.htm

Next, take a look at my example resuest-tracker.conf and include the lines that override the /helpdesk URL. Here's the snippet:
```
Alias /helpdesk /opt/RTIR_WHD_API/helpdesk/
        <Location /helpdesk>
            Order allow,deny
            Allow from all
        </Location>
        <Directory /opt/RTIR_WHD_API/helpdesk>
            AllowOverride all
        </Directory>
```

Next, test the installation!

https://support.example.com/helpdesk/WebObjects/Helpdesk.woa/ra/Preferences?apiKey=APIKEY

The above should show:
```json
{ "id": 1, "type": "Preference", "version": "SBS Emulator 1.1", "useRoomsBoolean": false, "roomPopup": false, "useDepartmentsBoolean": false, "defaultPriorityTypeId": 3, "defaultStatusTypeId": 1, "needsApprovalStatusTypeId": 7, "defaultNoteStatusTypeId": null, "billingEnabledBoolean": false, "ticketClientRequiredBoolean": true, "apiVersion": "12.1.0.276" }
```


https://support.example.com/helpdesk/WebObjects/Helpdesk.woa/ra/RequestTypes?apiKey=APIKEY

The above should show your RT queues in JSON format, like ths:

```json
[
  {
    "id": 1,
    "type": "RequestType",
    "parentId": null,
    "problemTypeName": "Client Support",
    "approvalProcess": null,
    "customFieldDefinitions": [
      
    ],
    "isLeafChild": true,
    "hasTechGroup": false,
    "autoAssignType": "None",
    "priorityTypeId": 3,
    "techGroupName": "Client Support",
    "sendClientEmailBoolean": true,
    "sendTechEmailBoolean": true,
    "sendLevelTechEmailBoolean": false,
    "sendGroupMgrEmailBoolean": false
  },
  {
    "id": 4,
    "type": "RequestType",
    "parentId": null,
    "problemTypeName": "Incident Reports",
    "approvalProcess": null,
    "customFieldDefinitions": [
      
    ],
    "isLeafChild": true,
    "hasTechGroup": false,
    "autoAssignType": "None",
    "priorityTypeId": 3,
    "techGroupName": "Incident Reports",
    "sendClientEmailBoolean": true,
    "sendTechEmailBoolean": true,
    "sendLevelTechEmailBoolean": false,
    "sendGroupMgrEmailBoolean": false
  }
]
```

Once that works, add a custom field for tickets in your RT installation called "AssetID", and assign it to the queues you want to use. Then, open N-Central, open the level above SO (whatever that's called) and go to Administration -> PSA Integrator -> Configure PSA Integration. Choose "Help Desk Manager", enter the root URL of your RT installation, like this: "https://support.example.com/" and ehter the API key that you created above in PSAIntegrator.php. Next, Save.. and configure just like you would if you were using Help Desk Manager.

==Some things to note...

In this version, locations are not used. We simply return "Main" to N-Central as a choice.

RequestTypes are associated with RT Queues. This seemed to be the most logical.

StatusTypes are hard coded. You could override these by copying the _construct entry in PSAIntegratorBase.php to PSAIntegrator.php and removing the call to the parent construct.

