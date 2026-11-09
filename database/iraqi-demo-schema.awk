# Retain the schema and application reference data from the bundled SQL snapshot.
/^INSERT INTO `/ {
    table = $0
    sub(/^INSERT INTO `/, "", table)
    sub(/`.*/, "", table)
    skip = table !~ /^(currencies|languages|migrations|permissions|sms_gateway|translations)$/
}
/^-- -+$/ { skip = 0 }
/AUTO_INCREMENT, AUTO_INCREMENT=[0-9]+;$/ { sub(/, AUTO_INCREMENT=[0-9]+;$/, ";") }
!skip { print }
