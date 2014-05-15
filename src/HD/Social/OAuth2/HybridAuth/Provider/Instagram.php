<?php
namespace HD\Social\OAuth2\HybridAuth\Provider;

/**
 * This is simply to trigger autoloading as a hack for poor design in HybridAuth.
 */
class Instagram extends \Hybrid_Providers_Instagram
{
    /**
     * load the user profile from the IDp api client
     */
    public function getUserProfile()
    {
        $data = $this->api->api("users/self/");

        if ($data->meta->code != 200) {
            throw new Exception("User profile request failed! {$this->providerId} returned an invalid response.", 6);
        }

        $this->user->profile->identifier  = $data->data->id;
        $this->user->profile->displayName = $data->data->username;
        $this->user->profile->description = $data->data->bio;
        $this->user->profile->photoURL    = $data->data->profile_picture;

        $this->user->profile->webSiteURL  = $data->data->website;

        return $this->user->profile;
    }
}
