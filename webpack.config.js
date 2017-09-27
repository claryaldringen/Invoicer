module.exports = {
	entry: './client/index.js',
	output: { path: __dirname + '/htdocs/js', filename: 'bundle.js' },
	module: {
		loaders: [
			{
				test: /.jsx?$/,
				loader: 'babel-loader',
				exclude: /node_modules/,
				query: {
					presets: ['es2015', 'react', 'stage-2']
				}
			}
		]
	},
	resolve: {
		extensions: ['.js', '.jsx']
	},
	watch: true,
	devtool: 'source-map'
};