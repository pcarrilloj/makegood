package com.piece_framework.makegood.launch.phpunit;

import java.util.Map;

public class Error {
    String type;
    String content;

    Error(Map<String, String> attributes) {
        if (attributes.containsKey("type")) {
            this.type = attributes.get("type");
        }
        if (attributes.containsKey("content")) {
            this.content = attributes.get("content");
        }
    }

    public String getType() {
        return type;
    }

    public String getContent() {
        return content;
    }
}
