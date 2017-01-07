<?php

namespace rajeshtomjoe\googlecontacts\factories;

use rajeshtomjoe\googlecontacts\helpers\GoogleHelper;
use rajeshtomjoe\googlecontacts\objects\Contact;

abstract class ContactFactory
{
    public static function getAll()
    {
        $response = GoogleHelper::getResponse('GET','https://www.google.com/m8/feeds/contacts/default/full?max-results=50');

        $xmlContacts = simplexml_load_string($response);
        $xmlContacts->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');

        $contactsArray = array();

        foreach ($xmlContacts->entry as $xmlContactsEntry) {
            $contactDetails = array();

            $contactDetails['id'] = (string) $xmlContactsEntry->id;
            $contactDetails['name'] = (string) $xmlContactsEntry->title;

            foreach ($xmlContactsEntry->children() as $key => $value) {
                $attributes = $value->attributes();

                if ($key == 'link') {
                    if ($attributes['rel'] == 'edit') {
                        $contactDetails['editURL'] = (string) $attributes['href'];
                    } elseif ($attributes['rel'] == 'self') {
                        $contactDetails['selfURL'] = (string) $attributes['href'];
                    }
                }
            }

            $contactGDNodes = $xmlContactsEntry->children('http://schemas.google.com/g/2005');
            foreach ($contactGDNodes as $key => $value) {
                switch ($key) {
                    case 'organization':
                        $contactDetails[$key]['orgName'] = (string) $value->orgName;
                        $contactDetails[$key]['orgTitle'] = (string) $value->orgTitle;
                        break;
                    case 'email':
                        $attributes = $value->attributes();
                        $emailadress = (string) $attributes['address'];
                        $emailtype = substr(strstr($attributes['rel'], '#'), 1);
                        $contactDetails[$key][$emailtype] = $emailadress;
                        break;
                    case 'phoneNumber':
                        $attributes = $value->attributes();
                        $uri = (string) $attributes['uri'];
                        $type = substr(strstr($attributes['rel'], '#'), 1);
                        $e164 = substr(strstr($uri, ':'), 1);
                        $contactDetails[$key][$type] = $e164;
                        break;
                    default:
                        $contactDetails[$key] = (string) $value;
                        break;
                }
            }

            $contactsArray[] = new Contact($contactDetails);
        }

        return $contactsArray;
    }

    public static function getBySelfURL($selfURL)
    {
        $response = GoogleHelper::getResponse('GET', $selfURL);

        $xmlContact = simplexml_load_string($response);
        $xmlContact->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');

        $xmlContactsEntry = $xmlContact;

        $contactDetails = array();

        $contactDetails['id'] = (string) $xmlContactsEntry->id;
        $contactDetails['name'] = (string) $xmlContactsEntry->title;

        foreach ($xmlContactsEntry->children() as $key => $value) {
            $attributes = $value->attributes();

            if ($key == 'link') {
                if ($attributes['rel'] == 'edit') {
                    $contactDetails['editURL'] = (string) $attributes['href'];
                } elseif ($attributes['rel'] == 'self') {
                    $contactDetails['selfURL'] = (string) $attributes['href'];
                }
            }
        }

        $contactGDNodes = $xmlContactsEntry->children('http://schemas.google.com/g/2005');

        foreach ($contactGDNodes as $key => $value) {
            $attributes = $value->attributes();

            if ($key == 'email') {
                $contactDetails[$key] = (string) $attributes['address'];
            } else {
                $contactDetails[$key] = (string) $value;
            }
        }

        return new Contact($contactDetails);
    }

    public static function submitUpdates(Contact $updatedContact)
    {
        $response = GoogleHelper::getResponse('GET', urldecode($updatedContact->selfURL));
        $xmlContact = simplexml_load_string($response);
        $xmlContact->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');

        $xmlContactsEntry = $xmlContact;

        $xmlContactsEntry->title = $updatedContact->name;

        $contactGDNodes = $xmlContactsEntry->children('http://schemas.google.com/g/2005');

        foreach ($contactGDNodes as $key => $value) {
            $attributes = $value->attributes();

            if ($key == 'email') {
                $attributes['address'] = $updatedContact->email;
            } else {
                $xmlContactsEntry->$key = $updatedContact->$key;
                $attributes['uri'] = '';
            }
        }

        $updatedXML = $xmlContactsEntry->asXML();

        $response = GoogleHelper::getResponse('PUT', urldecode($updatedContact->editURL), $updatedXML);

        $xmlContact = simplexml_load_string($response);
        $xmlContact->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');

        $xmlContactsEntry = $xmlContact;

        $contactDetails = array();

        $contactDetails['id'] = (string) $xmlContactsEntry->id;
        $contactDetails['name'] = (string) $xmlContactsEntry->title;

        foreach ($xmlContactsEntry->children() as $key => $value) {
            $attributes = $value->attributes();

            if ($key == 'link') {
                if ($attributes['rel'] == 'edit') {
                    $contactDetails['editURL'] = (string) $attributes['href'];
                } elseif ($attributes['rel'] == 'self') {
                    $contactDetails['selfURL'] = (string) $attributes['href'];
                }
            }
        }

        $contactGDNodes = $xmlContactsEntry->children('http://schemas.google.com/g/2005');

        foreach ($contactGDNodes as $key => $value) {
            $attributes = $value->attributes();

            if ($key == 'email') {
                $contactDetails[$key] = (string) $attributes['address'];
            } else {
                $contactDetails[$key] = (string) $value;
            }
        }

        return new Contact($contactDetails);
    }

    public static function create($name, $phoneNumber, $emailAddress, $groupId)
    {
        $doc = new \DOMDocument();
        $doc->formatOutput = true;
        $entry = $doc->createElement('atom:entry');
        $entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:atom', 'http://www.w3.org/2005/Atom');
        $entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gd', 'http://schemas.google.com/g/2005');
		$entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gContact', 'http://schemas.google.com/contact/2008');
        $doc->appendChild($entry);

        $title = $doc->createElement('title', $name); 
        $entry->appendChild($title);

        $email = $doc->createElement('gd:email');
        $email->setAttribute('rel', 'http://schemas.google.com/g/2005#work');
        $email->setAttribute('address', $emailAddress);
        $entry->appendChild($email);

        $contact = $doc->createElement('gd:phoneNumber', $phoneNumber);
        $contact->setAttribute('rel', 'http://schemas.google.com/g/2005#work');
        $entry->appendChild($contact);
		
		
		$group = $doc->createElement('gContact:groupMembershipInfo');
		$group->setAttribute('deleted', 'false');
		$group->setAttribute('href', $groupId); //userEmail cannot be replaced with default in https://www.google.com/m8/feeds/groups/{userEmail}/full  
		$entry->appendChild($group); 
		
        $xmlToSend = $doc->saveXML();

		try{
			$response = GoogleHelper::getResponse('POST', 'https://www.google.com/m8/feeds/contacts/default/full', $xmlToSend); 
		}catch(\GuzzleHttp\Exception\ClientException $e){
			echo ($e->getResponse()->getBody()->getContents());
			exit;
		}
        $xmlContact = simplexml_load_string($response);
        $xmlContact->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');

        $xmlContactsEntry = $xmlContact;

        $contactDetails = array();

        $contactDetails['id'] = (string) $xmlContactsEntry->id;
        $contactDetails['name'] = (string) $xmlContactsEntry->title;

        foreach ($xmlContactsEntry->children() as $key => $value) {
            $attributes = $value->attributes();

            if ($key == 'link') {
                if ($attributes['rel'] == 'edit') {
                    $contactDetails['editURL'] = (string) $attributes['href'];
                } elseif ($attributes['rel'] == 'self') {
                    $contactDetails['selfURL'] = (string) $attributes['href'];
                }
            }
        }

        $contactGDNodes = $xmlContactsEntry->children('http://schemas.google.com/g/2005');

        foreach ($contactGDNodes as $key => $value) {
            $attributes = $value->attributes();

            if ($key == 'email') {
                $contactDetails[$key] = (string) $attributes['address'];
            } else {
                $contactDetails[$key] = (string) $value;
            }
        }

        return new Contact($contactDetails);
    }
	
	public static function batchCreate($contactsArray, $groupId)
	//batch creates all contacts in the $contactsArray into the group $groupId, returns number created
    {
		$numCreated = 0;//counter
		
		//build a atom/xml document specifying contact information 
        $doc = new \DOMDocument();
        $doc->formatOutput = true;
        $feed = $doc->createElement('feed');
        $feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:atom', 'http://www.w3.org/2005/Atom');
        $feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gContact', 'http://schemas.google.com/contact/2008');
		$feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gd', 'http://schemas.google.com/g/2005');
		$feed->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:batch', 'http://schemas.google.com/gdata/batch');
        $doc->appendChild($feed);

		//make entries for each contact in $contactsArray
		foreach ($contactsArray as $contact){
			$entry = $doc ->createElement('entry');
			$feed->appendChild($entry);
		
			$id = $doc->createElement('batch:id', 'create'); 
			$entry->appendChild($id);
		
			$operation = $doc->createElement('batch:operation');
			$operation->setAttribute('type', 'insert');
			$entry->appendChild($operation);
		
			$category = $doc->createElement('category');
			$category->setAttribute('scheme', 'http://schemas.google.com/g/2005#kind');
			$category->setAttribute('term', 'http://schemas.google.com/g/2008#contact');
			$entry->appendChild($category);
		
			//set full name 
			$name = $doc->createElement('gd:name'); 
			$entry->appendChild($name);
			$fullName = $doc->createElement('gd:fullName', $contact["Name"]);
			$name->appendChild($fullName);

			//set eamil
			$email = $doc->createElement('gd:email');
			$email->setAttribute('rel', 'http://schemas.google.com/g/2005#work');
			$email->setAttribute('address', $contact["E-mail 1 - Value"]);
			$entry->appendChild($email);

			//set mobile phone number 
			if($contact["Phone 1 - Value"]){
				$phone = $doc->createElement('gd:phoneNumber', $contact["Phone 1 - Value"]);
				$phone->setAttribute('rel', 'http://schemas.google.com/g/2005#mobile');
				$entry->appendChild($phone);
			}
			
			//set home phone number  
			if($contact["Phone 2 - Value"]){
				$phone = $doc->createElement('gd:phoneNumber', $contact["Phone 2 - Value"]);
				$phone->setAttribute('rel', 'http://schemas.google.com/g/2005#home');
				$entry->appendChild($phone);
			}
			
			//set work phone number 
			if($contact["Phone 3 - Value"]){
				$phone = $doc->createElement('gd:phoneNumber', $contact["Phone 3 - Value"]);
				$phone->setAttribute('rel', 'http://schemas.google.com/g/2005#work');
				$entry->appendChild($phone);
			}
			
			//set group to $groupId 
			$group = $doc->createElement('gContact:groupMembershipInfo');
			$group->setAttribute('deleted', 'false');
			$group->setAttribute('href', $groupId);  
			$entry->appendChild($group); 
			
			$numCreated = $numCreated + 1;
		}
		
		
		//save everything that we've written above 
        $xmlToSend = $doc->saveXML();

		//send http POST request with the above xml to the contacts batch feed url 
		try{
			$response = GoogleHelper::getResponse('POST', 'https://www.google.com/m8/feeds/contacts/default/full/batch', $xmlToSend); 
		}catch(\GuzzleHttp\Exception\ClientException $e){
			echo ($e->getResponse()->getBody()->getContents());
			exit;
		}
		return $numCreated;
	}
	
	public static function createGroup($groupName)
	//creates a contact group with the specified $groupName, returns the $groupId ('http://www.google.com/m8/feeds/groups/test@example.com/base/asdfjkl1234) 
    {
        $doc = new \DOMDocument();
        $doc->formatOutput = true;
        $entry = $doc->createElement('atom:entry');
        $entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:atom', 'http://www.w3.org/2005/Atom');
        $entry->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:gd', 'http://schemas.google.com/g/2005');
        $doc->appendChild($entry);

        $title = $doc->createElement('atom:title', $groupName);
        $entry->appendChild($title);

        $xmlToSend = $doc->saveXML();

        $response = GoogleHelper::getResponse('POST', 'https://www.google.com/m8/feeds/groups/default/full', $xmlToSend); 
		$xmlGroup = simplexml_load_string($response);
		$groupId = (string) $xmlGroup->id;
		return $groupId;
	}
}
