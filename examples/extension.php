<?php
class ExtendedLumener extends Lumener
{
    protected $disabled = ["mysql", "information_schema"];

    public function databases($flush = true)
    {
        $return = array();
        foreach (get_databases($flush) as $db) {
            if (!in_array(strtolower($db), $this->disabled)) {
                $return[] = $db;
            }
        }
        return $return;
    }
}


if (empty($plugins)) {
    return new ExtendedLumener();
}
return new ExtendedLumener($plugins);
