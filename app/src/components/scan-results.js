import React from 'react'
import {Component} from '@wordpress/element'

import './scan-results.scss'

class ScanResults extends Component {
	constructor(props) {
		super(props);

		this.state = {
			loadingDiffs: false,
			diffs: [],
			error: null
		};
	}

	render() {
		const displayLines = (change, file) => {
			let lines = [], originalOffset = 0, patchedOffset = 0;
			let lineBreak = new RegExp(/\r?\n$|\r$/m);

			if (change.line[0] < change.line[1]) {
				originalOffset = change.line[1] - change.line[0];
				lines = lines.concat(change.original.slice(0, originalOffset).map(line => {
					return {
						line,
						op: 1
					}
				}));
			} else if(change.line[0] > change.line[1]) {
				patchedOffset = change.line[0] - change.line[1];
				lines = lines.concat(change.patched.slice(0, patchedOffset).map(line => {
					return {
						line,
						op: 2
					}
				}));
			}

			lines = lines.concat(change.original.slice(originalOffset).map(line => {
				return {
					line,
					op: 1
				}
			}));

			lines = lines.concat(change.patched.slice(patchedOffset).map(line => {
				return {
					line,
					op: 2
				}
			}));

			return (
				<div key={`${file}:${change.line[0]}:${change.line[1]}`} className="change">
					{lines.map(line => (
						<div className="line">
							<span>{(line.op === 1) ? '-' : '+'}</span>
							<code>{line.line.replace(lineBreak, '')}</code>
						</div>
					))}
				</div>
			);
		};

		return (
			<div className="pw__scan-results">

				{this.state.error && (
					<p className="error">{this.state.error.message}</p>
				)}

				{this.state.loadingDiffs ? (
					<div className="loading">
						Loading diffs
					</div>
				) : (
					<div className="diffsView">
						{this.state.diffs.map(diff => (
							<div key={diff.file} className="diff">
								<div className="heading">
									<div className="fileName">{diff.file}</div>
									<div className="changes">
										<span className="added">+{diff.lines_added}</span>
										,&nbsp;
										<span className="removed">-{diff.lines_deleted}</span>
									</div>
								</div>

								<div className="fileChanges">
									{diff.changes.map(displayLines, diff.file)}
								</div>

							</div>
						))}
					</div>
				)}
			
			</div>
		);
	}

	componentDidMount() {
		this.setState({
			loadingDiffs: true
		});

		patchwork.api.scan.scan(this.props.scanToken)
			.then(response => {
				console.log(response);
				this.setState({
					loadingDiffs: false,
					diffs: response
				});
			})
			.catch(error => {
				console.error(error)
				this.setState({
					loadingDiffs: false,
					error
				});
			})
	}
}

export default ScanResults;