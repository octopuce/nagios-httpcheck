class octopuce::nagios::commands::http_command {

    # Web checks
    nagios_command {

        # HTTP
        'check_http':
            command_line => '$USER1$/check_http -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ ;
        'check_http_status':
            command_line => '$USER1$/check_http -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -e $ARG5$';
        'check_http_regexp':
            command_line => '$USER1$/check_http -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -R $ARG5$%';
        'check_http_auth':
            command_line => '$USER1$/check_http -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$  -a $ARG5$';
        'check_http_status_regexp':
            command_line => '$USER1$/check_http -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -e $ARG5$ -R $ARG6$%';
        'check_http_status_auth':
            command_line => '$USER1$/check_http -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -e $ARG5$ -a $ARG6$';
        'check_http_regexp_invertregexp':
            command_line => '$USER1$/check_http -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -R $ARG5$% --invert-regex';
        'check_http_regexp_auth':
            command_line => '$USER1$/check_http -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -R $ARG5$% -a $ARG6$';
        'check_http_status_regexp_invertregexp':
            command_line => '$USER1$/check_http -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -e $ARG5$ -R $ARG6$% --invert-regex';
        'check_http_status_regexp_auth':
            command_line => '$USER1$/check_http -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -e $ARG5$ -R $ARG6$% -a $ARG7$';
        'check_http_regexp_invertregexp_auth':
            command_line => '$USER1$/check_http -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -R $ARG5$% --invert-regex -a $ARG6$';
        'check_http_status_regexp_invertregexp_auth':
            command_line => '$USER1$/check_http -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -e $ARG5$ -R $ARG6$% --invert-regex -a $ARG7$';

        # HTTPS
        'check_https':
            command_line => '$USER1$/check_http --ssl -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$';
        'check_https_status':
            command_line => '$USER1$/check_http --ssl -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -e $ARG5$';
        'check_https_regexp':
            command_line => '$USER1$/check_http --ssl -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -R $ARG5$%';
        'check_https_auth':
            command_line => '$USER1$/check_http --ssl -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$  -a $ARG5$';
        'check_https_status_regexp':
            command_line => '$USER1$/check_http --ssl -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -e $ARG5$ -R $ARG6$%';
        'check_https_status_auth':
            command_line => '$USER1$/check_http --ssl -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -e $ARG5$ -a $ARG6$';
        'check_https_regexp_invertregexp':
            command_line => '$USER1$/check_http --ssl -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -R $ARG5$% --invert-regex';
        'check_https_regexp_auth':
            command_line => '$USER1$/check_http --ssl -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -R $ARG5$% -a $ARG6$';
        'check_https_status_regexp_invertregexp':
            command_line => '$USER1$/check_http --ssl -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -e $ARG5$ -R $ARG6$% --invert-regex';
        'check_https_status_regexp_auth':
            command_line => '$USER1$/check_http --ssl -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -e $ARG5$ -R $ARG6$% -a $ARG7$';
        'check_https_regexp_invertregexp_auth':
            command_line => '$USER1$/check_http --ssl -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -R $ARG5$% --invert-regex -a $ARG6$';
        'check_https_status_regexp_invertregexp_auth':
            command_line => '$USER1$/check_http --ssl -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -e $ARG5$ -R $ARG6$% --invert-regex -a $ARG7$';

    }
}