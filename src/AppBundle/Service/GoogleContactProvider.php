<?php

namespace AppBundle\Service;

use Guzzle\Service\Client;

class GoogleContactProvider
{
    const URL_CONTACTS_GET = 'https://www.google.com/m8/feeds/contacts/default/full?max-results=10000&v=3.0&oauth_token=%s';

    /** @var Client */
    private $guzzle;

    /**
     * @param Client $guzzle
     */
    public function __construct(Client $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    public function getContactsByToken($token)
    {
        $url = sprintf(self::URL_CONTACTS_GET, $token);
        $request = $this->guzzle->get($url);
        $response = $request->send();
        $responseBody = (string)$response->getBody();
        $contacts = $this->decode($responseBody);

        return $contacts;
    }

    private function decode($response)
    {
        $xmlContacts = simplexml_load_string($response);
        $xmlContacts->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');

        foreach ($xmlContacts->entry as $xmlContactsEntry) {
            $contactDetails = array();

            $contactDetails['id'] = (string) $xmlContactsEntry->id;
            $contactDetails['name'] = (string) $xmlContactsEntry->title;

            foreach ($xmlContactsEntry->children() as $key => $value) {
                $attributes = $value->attributes();

                if ($key == 'link') {
                    if ($attributes['rel'] == 'edit') {
                        $contactDetails['editUrl'] = (string) $attributes['href'];
                    } elseif ($attributes['rel'] == 'self') {
                        $contactDetails['selfUrl'] = (string) $attributes['href'];
                    } elseif ($attributes['type'] == 'image/*') {
                        $contactDetails['image'] = (string) $attributes['href'];
                    }
                }
            }

            $contactGDNodes = $xmlContactsEntry->children('http://schemas.google.com/g/2005');

            foreach ($contactGDNodes as $key => $value) {
                $attributes = $value->attributes();

                if ($key == 'email') {
                    $contactDetails[$key] = (string)$attributes['address'];
                } elseif ($key == 'name') {
                    continue;
                } else {
                    $contactDetails[$key] = (string) $value;
                }
            }

            $contactsArray[] = $contactDetails;
        }

        return $contactsArray;
    }
}