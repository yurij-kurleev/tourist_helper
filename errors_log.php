<?php
function errors($i, $details=""){
    if(empty($details)){
        $details="Reason - unknown";
    }
    switch($i) {
        case 1:
                define(CONNECT_DB_ERROR, '{
                        "errors":[
                        {
                            "status": "401",
                            "source": {"descriptor": "link"},
                            "title": "Unable connect database",
                            "details": "'.$details.'"
                        }
                      ]
                    }');
                break;
        case 2:
                define(INIT_QUERY_ERROR, '{
                        "errors":[
                        {
                            "status": "500",
                            "source": {"descriptor": "stmt"},
                            "title": "Init problems",
                            "details": "'.$details.'"
                        }
                      ]
                    }');
                break;
        case 3:
                define(PREPARE_QUERY_ERROR, '{
                                "errors":[
                                {
                                    "status": "500",
                                    "source": {"function": "mysqli_stmt_prepare"},
                                    "title": "Query problems",
                                    "details": "'.$details.'"
                                }
                              ]
                            }');
                break;
        case 4:
                define(EXECUTE_QUERY_ERROR, '{
                                "errors":[
                                {
                                    "status": "500",
                                    "source": {"function": "mysqli_stmt_execute"},
                                    "title": "Execution problems",
                                    "details": "'.$details.'"
                                }
                              ]
                            }');
                break;
        case 5:
                define(EMPTY_RESULT_SET_ERROR, '{
                                "errors":[
                                {
                                    "status": "404",
                                    "source": {"parameter": "result"},
                                    "title": "Nothing found",
                                    "details": "Nothing was found by your request"
                                }
                              ]
                            }');
                break;
        case 6:
                define(EMPTY_PARAM_ERROR, '{
                                "errors":[
                                {
                                    "status": "400",
                                    "source": {"parameter": "variable"},
                                    "title": "Empty input parameter",
                                    "details": "No data from client got on server"
                                }
                              ]
                            }');
                break;
        case 7:
                define(UNKNOWN_HEADER_ERROR, '{
                                "errors":[
                                {
                                    "status": "501",
                                    "source": {"parameter": "header"},
                                    "title": "Unknown header",
                                    "details": "'.$details.' header not implemented on server"
                                }
                              ]
                            }');
                break;
    }
}
?>