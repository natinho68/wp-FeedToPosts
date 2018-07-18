<?php


class FeedToPosts_Controller
{
    /**
     * A reference to the class for retrieving our option values.
     *
     * @access private
     * @var    FeedToPosts_Deserializer
     */
    private $deserializer;

    /**
     * @var FeedToPosts_Serializer
     */
    private $serializer;

    /**
     * @var FeedToPosts_Notices
     */
    private $notices;


    /**
     * FeedToPosts_Controller constructor.
     * @param $serializer
     * @param $deserializer
     * @param $notices
     */
    public function __construct($serializer, $deserializer, $notices)
    {
        $this->serializer = $serializer;
        $this->deserializer = $deserializer;
        $this->notices = $notices;
    }

    /**
     * Get data and post it
     * @return bool
     */
    public function FeedToPosts_Feed()
    {
        if ($this->serializer->FeedToPosts_hasValidNonce()) {
            if (empty($this->deserializer->FeedToPosts_getValue('feed'))) {
                FeedToPosts_notices_addError('Please provide a JSON feed');
                return false;
            } else {
                // get feed and json decode
                $json = json_decode(file_get_contents($this->deserializer->FeedToPosts_getValue('feed')), true);
                // init post array
                $postIt = [];
                // parse posts items
                foreach ($json['items'] as $item) {
                    if (!array_key_exists('title', $item) || !array_key_exists('pubdate', $item) || !array_key_exists('description', $item)) {
                        FeedToPosts_notices_addError('Invalid JSON');
                        return false;
                    }
                    // convert date D-d-M H:i:s O to Y-m-d H:i:s
                    $dateFeed = $item['pubdate'];
                    $convertDate = date("Y-m-d H:i:s", strtotime($dateFeed));

                    $postIt['post_title'] = $item['title'];
                    $postIt['post_content'] = $item['description'];
                    $postIt['post_status'] = $this->deserializer->FeedToPosts_getValue('status');
                    $postIt['post_author'] = intval($this->deserializer->FeedToPosts_getValue('user'));
                    $postIt['post_date_gmt'] = $convertDate;
                    $postIt['post_category'] = [intval($this->deserializer->FeedToPosts_getValue('category'))];

                    if (isset($_POST['submit'])) {
                        if (post_exists($item['title'])) {
                            FeedToPosts_notices_addError('Posts already exists (Empty the trash if already delete it) !');
                            return false;
                        } else {
                            wp_insert_post($postIt);
                        }
                    }
                }
            }
            FeedToPosts_notices_addSuccess('Post generated');
        }
    }
}
