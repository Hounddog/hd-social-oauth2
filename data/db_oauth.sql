CREATE TABLE oauth_user_provider (
    provider VARCHAR(255) NOT NULL,
    provider_id VARCHAR(50) NOT NULL,
    user_id VARCHAR(255) NOT NULL
);

CREATE TABLE oauth_user_provider_access_tokens (
    access_token VARCHAR(255) NOT NULL,
    provider VARCHAR(255) NOT NULL,
    provider_id VARCHAR(255) NOT NULL,
    user_id VARCHAR(255) ,
    expires TIMESTAMP,
    scope VARCHAR(2000),
    CONSTRAINT access_token_pk PRIMARY KEY (access_token)
);