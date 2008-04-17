update documents set checked_out_user_id=null
WHERE checked_out_user_id=-10 and is_checked_out=0;