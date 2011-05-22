{% set start = pageOffset + 1 %}
{% set end = start + items|length - 1 %}
Now showing {{ start }}-{{ end }} out of {{ totalRecords }} items.