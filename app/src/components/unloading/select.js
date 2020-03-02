import React, { Fragment } from 'react';
import ReactDOM from 'react-dom';
import PropTypes from 'prop-types';
import './style.scss';

class SelectOptions extends React.Component {
  constructor(props) {
    super(props);
    // console.log(this.props);
    // this.props.options.map(option => {
    //   console.log(option);
    // });
    this.state = {
      name: this.props.name,
      value: this.props.value,
      options: this.props.options,
      nameValue: this.props.nameValue,
      filterSelect: [],
    }
    
    this.onChangeSelect = this.onChangeSelect.bind(this);
    this.handleInput = this.handleInput.bind(this);
    this.optionSelect = this.optionSelect.bind(this);
  }

  // 
  componentDidMount() {
    // console.log('-----',this.state.nameValue);
  }

  // Обработка кастомного селекта
  onChangeSelect(e) {
    let select = e.target.closest('.select-search-box');
    let selectContainer = select.querySelector('.select-search-box__select');
    if (selectContainer) {
      let input = select.querySelector('input[type="text"]');
      selectContainer.classList.toggle('select-search-box__select--display');
      select.classList.toggle('select-search-box-box__select--open');
      input.focus();
      input.selectionStart = input.value.length;

      if (input.value == '') {
        let textCurrentSelect = '';
        let selectedOptionSelect = selectContainer.querySelector('.select-search-box__option--selected');
        if (selectedOptionSelect) {
          input.value = selectedOptionSelect.textContent;
          this.setState({
            nameValue: input.value
          });
        }
      }
    }
  }

  // Обработка input text
  handleInput(e) {
    /*
    *  TODO
    */
    let filterSelect = this.state.options.filter(option => option.name).map(id => id.id);
    this.setState({
      nameValue: e.target.value,
      filterSelect: filterSelect,
    });
    let select = e.target.closest('.select-search-box');
    let opinions = select.querySelector('.select-search-box__options');
    opinions.innerHTML = '';

    let selectHTML;
    filterSelect.map(option => {
      console.log(option.id);
      if (option.id == this.state.opinions) {
        selectHTML += <li 
            key={option.id} 
            onClick={this.optionSelect}
            data-id={option.id}
            className="select-search-box__option select-search-box__row">
            {option.name}
          </li>
      }
    });

    opinions.innerHTML = selectHTML;
    console.log(this.state.filterSelect);

    /*
    * // TODO
    */
  }

  optionSelect(e) {
    let option = e.target;
    let id = Number(option.dataset.id);
    let text = option.textContent;
    let select = option.closest('.select-search-box');
    const inner = select.querySelector('input[type="text"]');
    select.querySelector('select').value = id;
    inner.value = text;
    select.classList.toggle('select-search-box-box__select--open');
    select.querySelector('.select-search-box__search span').innerHTML = text;
    this.setState({
      nameValue: text
    });

    let selectContainer = select.querySelector('.select-search-box__select');
    if (selectContainer) {
      selectContainer.classList.toggle('select-search-box__select--display');
      selectContainer.querySelector('.select-search-box__option--selected').classList.remove('select-search-box__option--selected');
      e.target.classList.add('select-search-box__option--selected');
    }
  }

  render() {
    return (
      <Fragment>
        <input 
          type="text" 
          onChange={this.handleInput}
          value={this.state.nameValue}
        />
        <div onClick={this.onChangeSelect} className="select-search-box__search">
          <span>{this.props.nameValue}</span>
        </div>
        <div className="select-search-box__select">
          <ul className="select-search-box__options">
            {this.props.options.map(option => {
              let current = 'select-search-box__option select-search-box__row';
              option.id == this.state.value ? current += " select-search-box__option--selected" : current = current;
              return (
                <li 
                  key={option.id} 
                  onClick={this.optionSelect}
                  data-id={option.id}
                  className={current}>
                  {option.name}
                </li>
              );
            })}
          </ul>
        </div>
      </Fragment>
    );
  }
}

class Select extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      name: this.props.name,
      value: this.props.value,
      style: this.props.style,
      handleChange: this.props.handleChange,
      options: this.props.options,
      nameValue: this.props.nameValue,
    }

    console.log(this.props);
  }

  render() {
    return (
      <Fragment>
        <div className="select-search-box select-search-box--select">
          <select
            name={this.props.name}
            value={this.props.value}
            style={this.props.style}
            onChange={this.props.handleChange}
            required
          >
            <option value="" disabled>{this.props.placeholder}</option>
            {this.props.options.map(option => {
              return (
                <option
                  key={option.id}
                  value={option.id}
                  label={option.name}>{option.name}
                </option>
              );
            })}
          </select>

          <SelectOptions 
            name={this.props.name}
            value={this.props.value}
            options={this.props.options}
            nameValue={this.props.nameValue}
          />
        </div>
      </Fragment>
    );
  }
}

export { Select, SelectOptions };