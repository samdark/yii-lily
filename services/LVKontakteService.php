<?php
/**
 * LVKontakteService class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LVKontakteService is a eauth service class.
 * It provides properties and fetching method for eauth extension to authenticate through Vkontakte OAuth service.
 *
 * @package application.modules.lily.services
 */


class LVKontakteService extends VKontakteOAuthService
{


    /**
     * Fetch attributes array.
     * @return boolean whether the attributes was successfully fetched.
     */
    protected function fetchAttributes()
    {
        $info = (array)$this->makeSignedRequest('https://api.vkontakte.ru/method/getProfiles', array(
            'query' => array(
                'uids' => $this->uid,
                //'fields' => '', // uid, first_name and last_name is always available
                'fields' => 'nickname, sex, bdate, city, country, timezone, photo, photo_medium, photo_big, photo_rec',
            ),
        ));

        $info = $info['response'][0];


        $this->attributes['id'] = $info->uid;
        $this->attributes['name'] = $info->first_name . ' ' . $info->last_name;
        $this->attributes['url'] = 'http://vk.com/id' . $info->uid;

        if (!empty($info->nickname))
            $this->attributes['username'] = $info->nickname;
        else
            $this->attributes['username'] = 'id' . $info->uid;
        $this->attributes['displayId'] = "{$this->attributes['name']} ({$this->attributes['username']})";
        $this->attributes['sex'] = $info->sex == 1 ? 0 : 1;

        $this->attributes['city'] = $info->city;
        $this->attributes['country'] = $info->country;

        $this->attributes['timezone'] = timezone_name_from_abbr('', $info->timezone * 3600, date('I'));
        ;

        if (isset($info->bdate)) {
            $count = 0;
            $pos = -1;
            while (($pos = strpos($info->bdate, '.', $pos + 1)) !== false) $count++;
            $this->attributes['birthday'] = Yii::app()->dateFormatter->formatDateTime(CDateTimeParser::parse($info->bdate, $count == 2 ? 'd.M.yyyy' : 'd.M'), 'medium', NULL);
        }
        $this->attributes['photo'] = $info->photo;
        $this->attributes['photo_medium'] = $info->photo_medium;
        $this->attributes['photo_big'] = $info->photo_big;
        $this->attributes['photo_rec'] = $info->photo_rec;
    }
}
