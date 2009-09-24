package com.piece_framework.makegood.launch.phpunit;

import java.util.ArrayList;
import java.util.Collections;
import java.util.List;

public abstract class TestResult {
    String name;
    List<TestResult> results;

    public String getName() {
        return name;
    }

    public List<TestResult> getTestResults() {
        if (results == null) {
            return null;
        }
        return Collections.unmodifiableList(results);
    }

    public TestResult findTestResult(String name) {
        if (results == null) {
            return null;
        }

        for (TestResult result: results) {
            if (result.getName().equals(name)) {
                return result;
            }
        }
        return null;
    }

    void addTestResult(TestResult result) {
        if (results == null) {
            results = new ArrayList<TestResult>();
        }
        results.add(result);
    }
}