set :application, "EIS"
set :domain,      "213.96.7.243"
set :deploy_to,   "/home/marc/eis"
set :app_path,    "app"

set :scm,         :git
set :repository,    "file:///home/marc/workspace/eis"
set :deploy_via, :rsync_with_remote_cache

# Or: `accurev`, `bzr`, `cvs`, `darcs`, `subversion`, `mercurial`, `perforce`, or `none`

set :model_manager, "doctrine"
# Or: `propel`

role :web,        domain          # Your HTTP server, Apache/etc
role :app,        domain          # This may be the same as your `Web` server
role :db,         domain, :primary => true       # This is where Symfony2 migrations will run

set  :use_sudo,      false
set  :keep_releases,  3

set :shared_files,      ["app/config/parameters.yml"]
set :shared_children,     [app_path + "/logs", web_path + "/uploads"]

set :use_composer, true

set :writable_dirs,       ["app/cache", "app/logs"]
set :webserver_user,      "www-data"
set :user,      "marc"
set :permission_method,   :acl
set :use_set_permissions, true



# Be more verbose by uncommenting the following line
# logger.level = Logger::MAX_LEVEL
