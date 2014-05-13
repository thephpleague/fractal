guard 'phpunit2', :tests_path => 'tests', :cli => '--colors' do
  # Watch tests files
  watch(%r{^.+Test\.php$})

  # Watch library files and run their tests
  watch(%r{^src/(.+)\.php}) { |m| "tests/#{m[1]}Test.php" }
end
